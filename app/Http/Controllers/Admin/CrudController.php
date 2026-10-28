<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Locale;
use App\Support\SafeUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Schema-driven CRUD. Subclasses declare a model, a field schema and the list
 * columns; index/create/edit/store/update/destroy and the Blade forms are
 * shared, so adding a managed resource is a matter of declaring it.
 *
 * Hooks for richer resources: $formView, formData(), extraRules() and
 * afterSave() — see ProductCrudController.
 */
abstract class CrudController extends Controller
{
    /** @var class-string<Model> */
    protected string $model;

    protected string $routeName;   // admin.products
    protected string $title;       // Products
    protected string $singular;    // Product
    protected string $orderBy      = 'id';
    protected string $orderDir     = 'asc';
    protected array  $searchable   = [];
    protected string $formView     = 'admin.crud.form';

    /** The record being created or edited, for fields() that depend on it. */
    protected ?Model $current = null;

    /** Columns shown in the listing: attribute => label */
    abstract protected function columns(): array;

    /** Editable fields: name => ['type' => ..., 'label' => ..., ...] */
    abstract protected function fields(): array;

    /** Validation rules keyed by field name. */
    abstract protected function rules(?Model $record = null): array;

    /** Rules for inputs that are not model columns (handled in afterSave). */
    protected function extraRules(?Model $record = null): array
    {
        return [];
    }

    /** Extra variables for the form view. */
    protected function formData(Model $record): array
    {
        return [];
    }

    /** Save relations and other non-column inputs. */
    protected function afterSave(Model $record, Request $request): void
    {
    }

    public function index(Request $request)
    {
        $q = is_string($request->query('q')) ? trim($request->query('q')) : '';

        $records = $this->model::query()
            ->when($q && $this->searchable, function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    foreach ($this->searchable as $column) {
                        $w->orWhere($column, 'like', "%{$q}%");
                    }
                });
            })
            ->orderBy($this->orderBy, $this->orderDir)
            ->paginate(20)
            ->withQueryString();

        return view('admin.crud.index', [
            'records'   => $records,
            'columns'   => $this->columns(),
            'routeName' => $this->routeName,
            'title'     => $this->title,
            'singular'  => $this->singular,
            'q'         => $q,
        ]);
    }

    public function create()
    {
        return $this->form(new $this->model, 'New '.strtolower($this->singular));
    }

    public function edit($id)
    {
        return $this->form($this->model::findOrFail($id), 'Edit '.strtolower($this->singular));
    }

    public function store(Request $request)
    {
        $this->current = new $this->model;
        $validated = $request->validate($this->rules() + $this->extraRules());
        $data = $this->prepare(Arr::only($validated, array_keys($this->fields())));

        $record = $this->model::create($data);
        $this->afterSave($record, $request);
        $this->saveTranslations($record, $request);

        return redirect()
            ->route($this->routeName.'.edit', $record->getKey())
            ->with('status', $this->singular.' created.');
    }

    public function update(Request $request, $id)
    {
        $record = $this->current = $this->model::findOrFail($id);
        $validated = $request->validate($this->rules($record) + $this->extraRules($record));
        $data = $this->prepare(Arr::only($validated, array_keys($this->fields())), $record);

        $record->update($data);
        $this->afterSave($record, $request);
        $this->saveTranslations($record, $request);

        return redirect()
            ->route($this->routeName.'.edit', $record->getKey())
            ->with('status', $this->singular.' saved.');
    }

    public function destroy($id)
    {
        $this->model::findOrFail($id)->delete();

        return redirect()
            ->route($this->routeName.'.index')
            ->with('status', $this->singular.' deleted.');
    }

    protected function form(Model $record, string $title)
    {
        $this->current = $record;
        $fields = $this->fields();

        return view($this->formView, [
            'record'            => $record,
            'fields'            => $fields,
            'routeName'         => $this->routeName,
            'title'             => $title,
            'singular'          => $this->singular,
            'hasUploads'        => $this->hasUploads(),
            'translationFields' => $this->translationFields($record, $fields),
            'translationLocales'=> Locale::where('is_default', false)->orderBy('sort_order')->get(),
        ] + $this->formData($record));
    }

    /** Form fields that have per-language values on this model. */
    protected function translationFields(Model $record, array $fields): array
    {
        if (! method_exists($record, 'translatableFields')) {
            return [];
        }

        return array_intersect_key($fields, array_flip($record->translatableFields()));
    }

    /** translations[km][field] → translations table. */
    protected function saveTranslations(Model $record, Request $request): void
    {
        if (! method_exists($record, 'setTranslations')) {
            return;
        }

        $fields = $this->translationFields($record, $this->fields());

        foreach ((array) $request->input('translations', []) as $locale => $values) {
            if (! is_string($locale) || ! preg_match('/^[a-z]{2,5}$/', $locale)) {
                continue;
            }

            $values = array_intersect_key((array) $values, $fields);

            foreach ($values as $field => $value) {
                if (($fields[$field]['type'] ?? '') === 'keyvalue') {
                    $parsed = $this->parseKeyValue((string) $value);
                    $values[$field] = $parsed ? json_encode($parsed, JSON_UNESCAPED_UNICODE) : null;
                }
            }

            $record->setTranslations($locale, $values);
        }
    }

    /** Normalise submitted values (checkboxes, JSON fields, option lists, uploads). */
    protected function prepare(array $data, ?Model $record = null): array
    {
        $request = request();

        foreach ($this->fields() as $name => $field) {
            $type = $field['type'] ?? 'text';

            if (! empty($field['virtual'])) {
                unset($data[$name]);   // handled in afterSave()
                continue;
            }

            if ($type === 'checkbox') {
                $data[$name] = (bool) ($data[$name] ?? false);
            }

            if ($type === 'json' && isset($data[$name])) {
                $decoded = json_decode((string) $data[$name], true);
                $data[$name] = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
            }

            if ($type === 'keyvalue' && array_key_exists($name, $data)) {
                $data[$name] = $this->parseKeyValue((string) $data[$name]) ?: null;
            }

            if ($type === 'select' && array_key_exists($name, $data) && $data[$name] === '') {
                $data[$name] = null;
            }

            if ($type === 'image' || $type === 'file') {
                if ($request->hasFile($name)) {
                    $data[$name] = $this->storeUpload($request->file($name), $field['folder'] ?? 'media');
                } elseif ($request->boolean($name.'_remove')) {
                    $data[$name] = null;
                } else {
                    unset($data[$name]);   // no new file: keep whatever is stored
                }
            }
        }

        return $data;
    }

    /** "value = Label" lines → ['value' => 'Label']. A line without "=" uses itself as both. */
    protected function parseKeyValue(string $text): array
    {
        $options = [];

        foreach (preg_split('/\R/u', $text) as $line) {
            if (trim($line) === '') {
                continue;
            }

            [$key, $label] = array_pad(array_map('trim', explode('=', $line, 2)), 2, null);
            $options[Str::slug($key, '_') ?: $key] = $label ?? $key;
        }

        return $options;
    }

    /** Move an upload into public/uploads/{folder} and return its relative path. */
    protected function storeUpload(UploadedFile $file, string $folder): string
    {
        return $folder.'/'.SafeUpload::move($file, public_path('uploads/'.$folder), SafeUpload::IMAGES);
    }

    /** True when the form needs a multipart encoding. */
    protected function hasUploads(): bool
    {
        foreach ($this->fields() as $field) {
            if (in_array($field['type'] ?? 'text', ['image', 'file'], true)) {
                return true;
            }
        }

        return false;
    }
}
