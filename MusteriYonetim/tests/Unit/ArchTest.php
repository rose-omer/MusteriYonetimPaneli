<?php

use Illuminate\Database\Eloquent\Model;

arch('modeller Eloquent modelidir')
    ->expect('App\Models')
    ->toExtend(Model::class);

arch('uygulama kodunda hata ayıklama çağrısı yok')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('enumlar string tabanlıdır')
    ->expect('App\Enums')
    ->toBeStringBackedEnums()
    ->ignoring('App\Enums\Concerns');
