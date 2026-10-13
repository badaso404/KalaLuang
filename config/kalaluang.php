<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Biaya Pemasangan Lowongan
    |--------------------------------------------------------------------------
    |
    | Tarif tunggal yang ditagihkan ke perusahaan setiap kali memasang atau
    | memperpanjang satu lowongan, beserta masa tayangnya dalam hari.
    |
    */

    'listing_price' => (int) env('KALALUANG_LISTING_PRICE', 100_000),

    'listing_duration_days' => (int) env('KALALUANG_LISTING_DURATION_DAYS', 30),

];
