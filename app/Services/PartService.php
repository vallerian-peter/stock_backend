<?php

namespace App\Services;

use App\Models\Part;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PartService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Part::query()
            ->with('category')
            ->latest()
            ->paginate($perPage);
    }

    protected function storeImage(UploadedFile $image, ?int $imageLastModifiedAt = null): string
    {
        $extension = $image->getClientOriginalExtension();
        $timestamp = $imageLastModifiedAt ?? now()->getTimestampMs();
        $fileName = $extension
            ? sprintf('%s-%s.%s', $timestamp, Str::uuid(), $extension)
            : sprintf('%s-%s', $timestamp, Str::uuid());

        return $image->storeAs('parts', $fileName, 'public');
    }

    public function store(array $data, ?UploadedFile $image = null): Part
    {
        $imageLastModifiedAt = isset($data['imageLastModifiedAt'])
            ? (int) $data['imageLastModifiedAt']
            : null;
        $imagePath = $image
            ? $this->storeImage($image, $imageLastModifiedAt)
            : null;

        try {
            return DB::transaction(function () use ($data, $imagePath, $imageLastModifiedAt): Part {
                $part = Part::query()->create([
                    'partName' => $data['partName'],
                    'partNumber' => $data['partNumber'],
                    'quantity' => $data['quantity'],
                    'price' => $data['price'],
                    'imageUrl' => $imagePath,
                    'imageLastModifiedAt' => $imagePath ? $imageLastModifiedAt : null,
                    'categoryId' => $data['categoryId'] ?? null,
                    'status' => $data['status'],
                ]);

                return $part->fresh('category');
            });
        } catch (Throwable $exception) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }
    }

    public function update(Part $part, array $data, ?UploadedFile $image = null): Part
    {
        $incomingImageLastModifiedAt = isset($data['imageLastModifiedAt'])
            ? (int) $data['imageLastModifiedAt']
            : null;
        $hasSameImageTimestamp = $image
            && $incomingImageLastModifiedAt
            && $part->imageLastModifiedAt === $incomingImageLastModifiedAt;
        $previousImagePath = $part->imageUrl;
        $nextImagePath = $image && !$hasSameImageTimestamp
            ? $this->storeImage($image, $incomingImageLastModifiedAt)
            : null;

        try {
            $updatedPart = DB::transaction(function () use ($part, $data, $nextImagePath, $incomingImageLastModifiedAt): Part {
                $part->update([
                    'partName' => $data['partName'] ?? $part->partName,
                    'partNumber' => $data['partNumber'] ?? $part->partNumber,
                    'quantity' => $data['quantity'] ?? $part->quantity,
                    'price' => $data['price'] ?? $part->price,
                    'imageUrl' => $nextImagePath ?? $part->imageUrl,
                    'imageLastModifiedAt' => $nextImagePath
                        ? $incomingImageLastModifiedAt
                        : $part->imageLastModifiedAt,
                    'categoryId' => array_key_exists('categoryId', $data)
                        ? $data['categoryId']
                        : $part->categoryId,
                    'status' => $data['status'] ?? $part->status,
                ]);

                return $part->fresh('category');
            });

            if ($nextImagePath && $previousImagePath) {
                Storage::disk('public')->delete($previousImagePath);
            }

            return $updatedPart;
        } catch (Throwable $exception) {
            if ($nextImagePath) {
                Storage::disk('public')->delete($nextImagePath);
            }

            throw $exception;
        }
    }

    public function destroy(Part $part): void
    {
        $imagePath = $part->imageUrl;

        DB::transaction(function () use ($part): void {
            $part->delete();
        });

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
