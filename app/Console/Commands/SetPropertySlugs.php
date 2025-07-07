<?php

namespace App\Console\Commands;

use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetPropertySlugs extends Command
{
    protected $signature = 'set:property-slugs';

    protected $description = 'Set slug_id for existing properties based on titles';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $properties = Property::all();

        foreach ($properties as $property) {
            $property->slug_id = Str::slug($property->title);
            $property->save();
        }

        $this->info('Slug values set for existing properties.');
    }
}
