<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

class Dramas
{
    public function showDramas()
    {
        $list = [
            '/bristol_energy', 'Bristol Energy',
            '/bristol_water', 'Bristol water',
            'SEND', 'Special Educational Needs Disability',
            '/cyclepaths', 'cyclepaths'
        ];



        class Topic
        {
            private string $slug;
            private string $description;
            private Topic|null $replaced_by;
        }

        return "Don't know what goes here.";
    }
}
