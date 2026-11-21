@php
    /** @var \App\Models\Customer $record */
    $record = $getRecord();
    $audits = $record->audits()->with('user')->limit(50)->get();
@endphp

@if ($audits->isEmpty())
    <div class="mtp-empty">
        <p class="mtp-empty__title">Henüz kayıt yok</p>
        <p class="mtp-empty__text">Bu müşteri oluşturulduğunda, güncellendiğinde ya da silindiğinde işlem burada görünür.</p>
    </div>
@else
    <ol class="mtp-timeline" aria-label="İşlem geçmişi">
        @foreach ($audits as $audit)
            <li class="mtp-timeline__item">
                <div class="mtp-timeline__head">
                    <span class="mtp-timeline__who">{{ $audit->user?->name ?? 'Sistem' }}</span>
                    <span class="mtp-timeline__what">{{ mb_strtolower($audit->actionLabel(), 'UTF-8') }}</span>
                    <time class="mtp-timeline__when" datetime="{{ $audit->created_at->toIso8601String() }}" title="{{ $audit->created_at->format('d.m.Y H:i') }}">
                        {{ $audit->created_at->locale('tr')->diffForHumans() }}
                    </time>
                </div>

                @if ($lines = $audit->changeLines())
                    <dl class="mtp-timeline__changes">
                        @foreach ($lines as $line)
                            <div class="mtp-timeline__change">
                                <dt>{{ $line['label'] }}</dt>
                                <dd>
                                    <span class="mtp-timeline__old">{{ $line['old'] }}</span>
                                    <span aria-hidden="true" class="mtp-timeline__arrow">
                                        <x-filament::icon icon="heroicon-m-arrow-long-right" class="size-4" />
                                    </span>
                                    <span class="sr-only">yerine</span>
                                    <span class="mtp-timeline__new">{{ $line['new'] }}</span>
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </li>
        @endforeach
    </ol>
@endif
