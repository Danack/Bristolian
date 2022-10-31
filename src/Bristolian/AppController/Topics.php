<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

class Topics
{
    public function index()
    {
//        $list = [
//            '/bristol_energy', 'Bristol Energy',
//            '/bristol_water', 'Bristol water',
//            'SEND', 'Special Educational Needs Disability',
//            '/cyclepaths', 'cyclepaths'
//        ];


        $topics = [];

        'present',
        'now',
        'future',

        'local',
        'visitors',
        'new to bristol'


        $topics['council stuff'] = [
            '/bristol_energy', 'Bristol Energy',
            '/bristol_water', 'Bristol water',
            'SEND', 'Special Educational Needs Disability',
            '/cyclepaths', 'cyclepaths'
            'Mayor Rees'

        'Cllr Don Alexander'

        ];



        "public issues"
        // abandoned buildings.
        // https://twitter.com/DanicaPriest/status/1556755113510932481



//        <p>
//    Most of the actual features of that tool won't be available for a while, but I needed to put something online due to Bristol City Council are proposing to <a href="https://bristol.citizenspace.com/sustainable-transport/whiteladies-road-flood-alleviation-consultation/">remove a cycle lane</a> for dumb reasons, and I needed to put the site live so that I could make a page about that road.
//</p>


//        class Topic
//        {
//            private string $slug;
//            private string $description;
//            private Topic|null $replaced_by;
//        }

        return "Don't know what goes here.";
    }
}
