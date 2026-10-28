@extends('admin.layout')

@section('title', 'Import products')

@section('content')
    @error('import') <p class="alert alert--error" role="alert">{{ $message }}</p> @enderror

    @if ($log)
        <div class="card">
            <div class="card__head"><h2>Result</h2></div>
            <div class="card__body"><pre class="log">{{ implode("\n", $log) }}</pre></div>
        </div>
    @endif

    <div class="card">
        <div class="card__head"><h2>Product folder</h2><code>{{ $folder }}</code></div>
        <div class="card__body stack">
            @if ($files->isEmpty())
                <p class="empty">The folder is empty or missing. Add the product list (.xlsx or .csv) and the pack shots, or upload them below.</p>
            @else
                <ul class="file-list">
                    @foreach ($files as $file)<li><code>{{ $file }}</code></li>@endforeach
                </ul>
                <form method="post" action="{{ route('admin.import.folder') }}" class="stack">
                    @csrf
                    <label class="checks__item"><input type="checkbox" name="deactivate_missing" value="1"> Hide products that are not in the list</label>
                    <div class="form-actions">
                        <button class="btn btn--ghost" type="submit" name="dry_run" value="1">Preview</button>
                        <button class="btn btn--primary" type="submit">Import</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card__head"><h2>Upload a product list</h2></div>
        <div class="card__body">
            <form method="post" action="{{ route('admin.import.upload') }}" enctype="multipart/form-data" class="form-grid">
                @csrf
                <div class="field">
                    <label for="sheet">Product list (.xlsx or .csv)</label>
                    <input type="file" id="sheet" name="sheet" accept=".xlsx,.csv" required>
                    @error('sheet') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label for="images">Pack shots</label>
                    <input type="file" id="images" name="images[]" accept="image/*" multiple>
                    <p class="help">Matched to rows by dosage form, count and strength in the file name, e.g. "Bioflor 250mg B.10Sachet.png".</p>
                    @error('images.*') <p class="error">{{ $message }}</p> @enderror
                </div>
                <label class="checks__item"><input type="checkbox" name="deactivate_missing" value="1"> Hide products that are not in the list</label>
                <div class="form-actions"><button class="btn btn--primary" type="submit">Upload and import</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card__head"><h2>List format</h2></div>
        <div class="card__body stack">
            <p>The first row with a <strong>Description</strong> (or Product / Name) header is the header row. Recognised columns:</p>
            <table>
                <thead><tr><th>Column</th><th>Becomes</th></tr></thead>
                <tbody>
                    <tr><td>Description / Product / Name</td><td>Product name — strength and dosage form are read from it ("BIOFLOR 250 MG 10 SACHETS")</td></tr>
                    <tr><td>Composition / Ingredient</td><td>Active ingredient, and the Ingredients text ("… — 250 mg per sachet")</td></tr>
                    <tr><td>Packaging</td><td>Pack size label and units per pack ("10 sachets")</td></tr>
                    <tr><td>Indication / Usage</td><td>Indications, one per line</td></tr>
                    <tr><td>Dosage, Storage, Precautions, How to use, Age</td><td>The product field of the same name</td></tr>
                    <tr><td>Id / Ref / SKU (number)</td><td>Product id in the URL (optional)</td></tr>
                    <tr><td>Any other column</td><td>A new attribute with that name, shown in Key facts</td></tr>
                </tbody>
            </table>
            <p>Rows without a product name add lines to the product above (one indication or storage line per row).
                Below the table, product <strong>questions and answers</strong> are read: a heading naming the format
                ("… Sachet", "Bioflor® 250 mg Capsules"), then each question (ending with "?") followed by its answer.
                When the folder has several lists, the newest file is used.</p>
            <p class="help">Changed English text removes its Khmer version, so the Khmer page shows English until it is translated again (the Launch checklist lists what is missing).</p>
            <p class="help">Same importer from the command line: <code>php artisan catalog:import Product --dry-run</code></p>
        </div>
    </div>
@endsection
