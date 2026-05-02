<?php

use App\Models\Event;
use App\Models\User;

beforeEach(function () {
    $this->seed([
        PermissionSeeder::class,
        UserSeeder::class,
        EventSeeder::class,
    ]);
});

test('seeded event exists', function () {
    $events = Event::all();

    expect($events)->not->toBeEmpty();
});

test('seeded event organizer exists', function () {
    $events = Event::all();

    $events->each(function ($event) {
        $this->assertNotNull($event->organizer_id);
    });
});

test('seeded event organizer has organizer role', function () {
    $events = Event::all();

    $events->each(function ($event) {
        // Get user associated with organizer_id
        $organizer = User::find($event->organizer_id);
        $this->assertTrue($organizer->hasRole('organizer'));
    });
});
