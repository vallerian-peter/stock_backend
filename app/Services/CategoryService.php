<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $data): Category
    {
        return DB::transaction(function () use ($data): Category {
            $category = Category::query()->create($data);

            return $category->fresh();
        });
    }

    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data): Category {
            $category->update([
                'name' => $data['name'] ?? $category->name,
            ]);

            return $category->fresh();
        });
    }

    public function destroy(Category $category): void
    {
        DB::transaction(function () use ($category): void {
            $category->delete();
        });
    }
}
