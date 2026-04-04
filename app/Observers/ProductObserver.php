<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
        ]);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $changes = $product->getChanges();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'changes' => $changes,
        ]);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
        ]);
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'restored',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
        ]);
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'force_deleted',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
        ]);
    }
}
