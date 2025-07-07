<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:all-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and set unique slugs for existing records';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setUniqueSlugs(Property::class, 'title');
        $this->setUniqueSlugs(Category::class, 'category');
        $this->setUniqueSlugs(Article::class, 'title');

        $this->info('Slug values set for existing records.');
    }

    private function setUniqueSlugs($modelClass, $titleColumn)
    {
        $records = $modelClass::all();

        foreach ($records as $record) {
            $originalSlug = Str::slug($record->$titleColumn);
            $slug = $this->generateUniqueSlug($modelClass, $originalSlug, $record->id);

            $record->slug_id = $slug;
            $record->save();
        }
    }

    private function generateUniqueSlug($modelClass, $originalSlug, $recordId = null)
    {
        $slug = $originalSlug;

        $existingRecords = $modelClass::where('slug_id', $slug);

        if ($recordId) {
            $existingRecords->where('id', '!=', $recordId);
        }

        $counter = 1;

        while ($existingRecords->count() > 0) {
            $slug = $originalSlug . '-' . $counter;
            $existingRecords = $modelClass::where('slug_id', $slug);

            if ($recordId) {
                $existingRecords->where('id', '!=', $recordId);
            }

            $counter++;
        }

        return $slug;
    }
}
