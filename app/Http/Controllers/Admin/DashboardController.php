<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\NewsletterSubscriber;
use App\Models\PageSection;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Retailer;
use App\Models\Review;
use App\Support\LaunchChecklist;

class DashboardController extends Controller
{
    public function __invoke(LaunchChecklist $checklist)
    {
        $tasks = $checklist->tasks();

        return view('admin.dashboard', [
            'checklist'     => $tasks,
            'checklistNext' => $tasks->where('done', false)->take(3),
            'stats' => [
                ['label' => 'Products',      'value' => Product::where('is_active', true)->count(), 'route' => 'admin.products.index'],
                ['label' => 'Attributes',    'value' => ProductAttribute::count(),     'route' => 'admin.attributes.index'],
                ['label' => 'Categories',    'value' => Category::count(),             'route' => 'admin.categories.index'],
                ['label' => 'Articles',      'value' => Article::count(),              'route' => 'admin.articles.index'],
                ['label' => 'Page sections', 'value' => PageSection::count(),          'route' => 'admin.sections.index'],
                ['label' => 'Q&A entries',   'value' => Faq::count(),                  'route' => 'admin.faqs.index'],
                ['label' => 'Retailers',     'value' => Retailer::count(),             'route' => 'admin.retailers.index'],
                ['label' => 'Subscribers',   'value' => NewsletterSubscriber::count(), 'route' => 'admin.subscribers'],
            ],
            'pendingReviews' => Review::where('is_approved', false)->count(),
            'latestMessages' => ContactMessage::latest()->take(5)->get(),
            'latestSubs'     => NewsletterSubscriber::latest()->take(5)->get(),
            'draftArticles'  => Article::whereNull('published_at')->count(),
        ]);
    }
}
