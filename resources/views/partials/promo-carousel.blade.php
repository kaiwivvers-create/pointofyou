@if(isset($promos) && $promos->isNotEmpty())
    <section class="mb-10">
        <div class="flex items-end justify-between gap-4 mb-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-amber-700">Promotions</p>
                <h2 class="font-display text-2xl md:text-3xl font-bold text-amber-950">Current offers</h2>
            </div>
            <div class="hidden md:flex items-center gap-2 text-sm font-semibold text-stone-500">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Swipe or drag to browse
            </div>
        </div>

        @if($promos->count() === 1)
            @php
                $promo = $promos->first();
                $promoImage = $promo->image ? asset('app-storage/' . $promo->image) : 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1200&q=80';
                $promoTitle = $promo->title ?: 'Special promotion';
                $promoDescription = $promo->description ?: 'Limited-time offer available now.';
                $promo->load('rules.buyItem', 'rules.getItem');
                $promoDetailsText = '';
                if ($promo->rules && $promo->rules->isNotEmpty()) {
                    foreach ($promo->rules as $rule) {
                        if ($rule->buyItem) {
                            $promoDetailsText .= 'Buy ' . $rule->buy_quantity . 'x ' . $rule->buyItem->name;
                        }
                        if ($rule->getItem) {
                            $promoDetailsText .= ($promoDetailsText ? ', ' : '') . 'Get ' . $rule->get_quantity . 'x ' . $rule->getItem->name;
                        }
                    }
                }
                if ($promo->discount_value) {
                    $promoDetailsText .= ($promoDetailsText ? ', ' : '') . 'For $' . $promo->discount_value;
                }
            @endphp
            <div class="relative overflow-hidden rounded-[2rem] border border-amber-200/70 bg-white shadow-[0_20px_50px_rgba(120,53,15,0.08)] cursor-pointer hover:shadow-[0_25px_60px_rgba(120,53,15,0.12)] transition-shadow"
                 data-promo-id="{{ $promo->id }}"
                 data-promo='{{ $promo->toJson() }}'
                 data-promo-image="{{ $promoImage }}">
                <div class="grid gap-0 md:grid-cols-[1.15fr_0.85fr]">
                    <div class="relative min-h-[300px] md:min-h-[360px] overflow-hidden select-none text-white">
                        <div class="absolute inset-0 z-0">
                            <img src="{{ $promoImage }}" alt="{{ $promoTitle }}" draggable="false" ondragstart="return false;" class="h-full w-full object-cover select-none pointer-events-none" style="-webkit-user-drag: none; user-drag: none;">
                            <div class="absolute inset-0" style="background: rgba(0, 0, 0, 0.84);"></div>
                        </div>
                        <div class="relative z-20 flex h-full flex-col justify-between p-6 md:p-10">
                            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-white/20 bg-amber-500/85 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-white shadow-sm">
                                Featured
                            </div>
                            <div class="max-w-xl">
                                <h3 class="font-display text-3xl md:text-5xl font-bold leading-tight drop-shadow-md">{{ $promoTitle }}</h3>
                                @if($promoDetailsText)
                                    <p class="mt-4 max-w-lg text-base md:text-lg leading-relaxed text-white/95 font-semibold">{{ $promoDetailsText }}</p>
                                @endif
                                <p class="mt-2 max-w-lg text-base md:text-lg leading-relaxed text-white/95">{{ $promoDescription }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-center bg-[#fffaf3] p-6 md:p-10 select-none">
                        <div class="relative w-full max-w-sm overflow-hidden rounded-[1.75rem] border border-amber-200 bg-white shadow-xl shadow-amber-950/5 select-none">
                            <div class="aspect-[4/3] bg-stone-100">
                                <img src="{{ $promoImage }}" alt="{{ $promoTitle }}" draggable="false" ondragstart="return false;" class="h-full w-full object-cover select-none pointer-events-none" style="-webkit-user-drag: none; user-drag: none;">
                            </div>
                            <div class="p-5 md:p-6">
                                <div class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-amber-800">
                                    Featured offer
                                </div>
                                <h4 class="mt-3 font-display text-2xl font-bold text-amber-950">{{ $promoTitle }}</h4>
                                <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $promoDescription }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div id="promoBanner" class="relative overflow-hidden cursor-grab active:cursor-grabbing rounded-[2rem] border border-amber-200/70 bg-white shadow-[0_20px_50px_rgba(120,53,15,0.08)] touch-pan-y">
                <div id="promoContent" class="flex w-[500%] select-none">
                    @for ($repeat = 0; $repeat < 5; $repeat++)
                        @foreach($promos as $promo)
                            @php
                                $promoImage = $promo->image ? asset('app-storage/' . $promo->image) : 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1200&q=80';
                                $promoTitle = $promo->title ?: 'Special promotion';
                                $promoDescription = $promo->description ?: 'Limited-time offer available now.';
                                $promo->load('rules.buyItem', 'rules.getItem');
                                $promoDetailsText = '';
                                if ($promo->rules && $promo->rules->isNotEmpty()) {
                                    foreach ($promo->rules as $rule) {
                                        if ($rule->buyItem) {
                                            $promoDetailsText .= 'Buy ' . $rule->buy_quantity . 'x ' . $rule->buyItem->name;
                                        }
                                        if ($rule->getItem) {
                                            $promoDetailsText .= ($promoDetailsText ? ', ' : '') . 'Get ' . $rule->get_quantity . 'x ' . $rule->getItem->name;
                                        }
                                    }
                                }
                                if ($promo->discount_value) {
                                    $promoDetailsText .= ($promoDetailsText ? ', ' : '') . 'For $' . $promo->discount_value;
                                }
                            @endphp
                            <article class="promo-slide min-w-full flex-shrink-0 cursor-pointer"
                                     data-promo-id="{{ $promo->id }}"
                                     data-promo='{{ $promo->toJson() }}'
                                     data-promo-image="{{ $promoImage }}">
                                <div class="grid gap-0 md:grid-cols-[1.15fr_0.85fr]">
                                    <div class="relative min-h-[260px] md:min-h-[320px] bg-gradient-to-br from-amber-950 via-amber-900 to-stone-900 text-white overflow-hidden select-none">
                                        <div class="absolute inset-0 z-0">
                                            <img src="{{ $promoImage }}" alt="{{ $promoTitle }}" draggable="false" ondragstart="return false;" class="relative z-0 h-full w-full object-cover select-none pointer-events-none" style="-webkit-user-drag: none; user-drag: none;">
                                            <div class="absolute inset-0 z-10" style="background: rgba(0, 0, 0, 0.84);"></div>
                                        </div>
                                        <div class="relative z-20 flex h-full flex-col justify-between p-6 md:p-10 select-none">
                                            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-white/20 bg-black/45 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em]">
                                                Promo
                                            </div>
                                            <div class="max-w-xl select-none">
                                                <h3 class="font-display text-3xl md:text-5xl font-bold leading-tight drop-shadow-md">{{ $promoTitle }}</h3>
                                                @if($promoDetailsText)
                                                    <p class="mt-4 max-w-lg text-base md:text-lg leading-relaxed text-white/95 font-semibold">{{ $promoDetailsText }}</p>
                                                @endif
                                                <p class="mt-2 max-w-lg text-base md:text-lg leading-relaxed text-white/95">{{ $promoDescription }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative flex items-center justify-center bg-[#fffaf3] p-6 md:p-10 select-none">
                                        <div class="relative w-full max-w-sm overflow-hidden rounded-[1.75rem] border border-amber-200 bg-white shadow-xl shadow-amber-950/5 select-none">
                                            <div class="aspect-[4/3] bg-stone-100">
                                                <img src="{{ $promoImage }}" alt="{{ $promoTitle }}" draggable="false" ondragstart="return false;" class="h-full w-full object-cover select-none pointer-events-none" style="-webkit-user-drag: none; user-drag: none;">
                                            </div>
                                            <div class="p-5 md:p-6">
                                                <h4 class="font-display text-2xl font-bold text-amber-950">{{ $promoTitle }}</h4>
                                                <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $promoDescription }}</p>
                                                @if($promo->order !== null)
                                                    <div class="mt-4 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-amber-800">
                                                        Featured offer #{{ $promo->order }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    @endfor
                </div>
            </div>
        @endif
    </section>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Add click listeners to single promo elements (not in carousel)
        const promoElements = document.querySelectorAll('[data-promo]:not(.promo-slide)');
        console.log('Found single promo elements:', promoElements.length);
        promoElements.forEach(element => {
            element.addEventListener('click', (event) => {
                console.log('Single promo clicked');
                try {
                    const promoData = element.getAttribute('data-promo');
                    const promoImage = element.getAttribute('data-promo-image');
                    console.log('Promo data:', promoData);
                    if (promoData && promoImage) {
                        const promo = JSON.parse(promoData);
                        if (typeof window.openPromoModal === 'function') {
                            window.openPromoModal(promo, promoImage);
                        } else {
                            console.error('window.openPromoModal is not defined');
                        }
                    }
                } catch (e) {
                    console.error('Error handling promo click:', e);
                }
            });
        });
    });
</script>

@if($promos->count() > 1)
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const promoBanner = document.getElementById('promoBanner');
        const promoContent = document.getElementById('promoContent');

        if (!promoBanner || !promoContent) {
            return;
        }

        const originalCount = {{ $promos->count() }};
        if (originalCount < 1) {
            return;
        }

        let offset = 0;
        let frameId = null;
        let isDragging = false;
        let isPaused = false;
        let dragStartX = 0;
        let dragStartOffset = 0;
        let lastTimestamp = 0;
        let ignoreClick = false;

        const getSingleTrackWidth = () => promoContent.scrollWidth / 5;
        const getSlideWidth = () => getSingleTrackWidth() / originalCount;

        const applyOffset = () => {
            promoContent.style.transform = `translate3d(${-offset}px, 0, 0)`;
        };

        const normalizeOffset = () => {
            const trackWidth = getSingleTrackWidth();
            if (!trackWidth) {
                return;
            }

            const minOffset = trackWidth * 2;
            const maxOffset = trackWidth * 3;

            if (offset < minOffset) {
                offset += trackWidth;
            } else if (offset >= maxOffset) {
                offset -= trackWidth;
            }

            applyOffset();
        };

        const stopLoop = () => {
            if (frameId !== null) {
                window.cancelAnimationFrame(frameId);
                frameId = null;
            }
        };

        const startLoop = () => {
            stopLoop();

            const step = (timestamp) => {
                if (!lastTimestamp) {
                    lastTimestamp = timestamp;
                }

                const elapsed = timestamp - lastTimestamp;
                lastTimestamp = timestamp;

                if (!isPaused && !isDragging) {
                    const slideWidth = getSlideWidth();
                    const speed = slideWidth / 24000;
                    offset += speed * elapsed;
                    normalizeOffset();
                } else {
                    lastTimestamp = timestamp;
                }

                frameId = window.requestAnimationFrame(step);
            };

            frameId = window.requestAnimationFrame(step);
        };

        promoBanner.addEventListener('pointerdown', (event) => {
            console.log('Pointer down');
            isDragging = true;
            isPaused = true;
            ignoreClick = false;
            dragStartX = event.pageX;
            dragStartOffset = offset;
        });

        promoBanner.addEventListener('pointermove', (event) => {
            if (!isDragging) {
                return;
            }

            event.preventDefault();
            const delta = event.pageX - dragStartX;
            if (Math.abs(delta) > 10) {
                ignoreClick = true;
            }

            offset = dragStartOffset - delta;
            normalizeOffset();
        });

        const endDrag = () => {
            if (!isDragging) {
                return;
            }

            isDragging = false;
            window.setTimeout(() => {
                isPaused = false;
            }, 50);
        };

        promoBanner.addEventListener('pointerup', endDrag);
        promoBanner.addEventListener('pointercancel', endDrag);
        promoBanner.addEventListener('lostpointercapture', endDrag);

        // Prevent clicks only when dragging occurred, otherwise handle promo clicks
        promoBanner.addEventListener('click', (event) => {
            console.log('Carousel click, ignoreClick:', ignoreClick);
            if (ignoreClick) {
                event.preventDefault();
                event.stopPropagation();
                ignoreClick = false;
                return;
            }

            // Check if click was on a promo element
            const promoElement = event.target.closest('[data-promo]');
            console.log('Promo element found:', promoElement);
            if (promoElement) {
                console.log('Promo clicked via carousel');
                try {
                    const promoData = promoElement.getAttribute('data-promo');
                    const promoImage = promoElement.getAttribute('data-promo-image');
                    console.log('Promo data:', promoData);
                    if (promoData && promoImage) {
                        const promo = JSON.parse(promoData);
                        if (typeof window.openPromoModal === 'function') {
                            window.openPromoModal(promo, promoImage);
                        } else {
                            console.error('window.openPromoModal is not defined');
                        }
                    }
                } catch (e) {
                    console.error('Error handling promo click:', e);
                }
            }
        });

        window.addEventListener('resize', () => {
            normalizeOffset();
        });

        window.requestAnimationFrame(() => {
            offset = getSingleTrackWidth() * 2;
            normalizeOffset();
            startLoop();
        });
    });
</script>
@endif
