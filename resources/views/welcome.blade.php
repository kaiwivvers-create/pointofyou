<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Golden Crumb</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fredoka:400,500,600,700|nunito:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-stone-800 bg-[#faf6f0] overflow-x-hidden">

    <header class="w-full sticky top-0 z-50 bg-[#faf6f0]/90 backdrop-blur-md border-b border-amber-200/60">
        <div class="w-full px-4 sm:px-8 lg:px-14 py-4 flex items-center justify-between gap-4">
            <a href="#" class="flex items-center gap-2 shrink-0">
                <span class="text-2xl" aria-hidden="true">🥐</span>
                <span class="font-display text-xl sm:text-2xl font-medium text-amber-950">Golden Crumb</span>
            </a>
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-stone-600">
                <a href="#treats" class="hover:text-amber-800 transition-colors">Our Bakes</a>
                <a href="#about" class="hover:text-amber-800 transition-colors">About</a>
                <a href="#order" class="hover:text-amber-800 transition-colors">Order</a>
            </nav>
            <a href="/kiosk" class="shrink-0 rounded-full bg-amber-800 px-5 py-2.5 text-sm font-semibold text-amber-50 hover:bg-amber-900 transition-colors">
                Order Now
            </a>
        </div>
    </header>

    <section class="w-full relative min-h-[85vh] flex items-center">
        <div class="absolute inset-0 w-full">
            <img
                src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80"
                alt="Fresh bread and pastries on a wooden table"
                class="w-full h-full object-cover"
            >
            <div class="absolute inset-0 bg-gradient-to-r from-amber-950/85 via-amber-900/70 to-amber-950/40"></div>
        </div>
        <div class="relative w-full px-4 sm:px-8 lg:px-14 py-20 lg:py-28">
            <div class="max-w-3xl">
                <p class="text-amber-200/90 text-sm sm:text-base font-medium mb-4">Artisan bakery since 2018 ✨</p>
                <h1 class="font-display text-4xl sm:text-5xl lg:text-7xl font-semibold text-amber-50 leading-[1.15] mb-6">
                    Baked with love,<br class="hidden sm:block"> served with warmth
                </h1>
                <p class="text-lg sm:text-xl text-amber-100/90 max-w-xl mb-10 leading-relaxed">
                    Sourdough loaves, buttery croissants, and celebration cakes — made fresh every morning in our neighborhood kitchen.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="#treats" class="inline-flex justify-center items-center rounded-full bg-amber-50 px-8 py-4 text-base font-semibold text-amber-950 hover:bg-white transition-colors">
                        See today's bakes
                    </a>
                    <a href="#about" class="inline-flex justify-center items-center rounded-full border-2 border-amber-200/60 px-8 py-4 text-base font-semibold text-amber-50 hover:bg-amber-50/10 transition-colors">
                        Our story
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="w-full bg-amber-800 text-amber-50">
        <div class="w-full px-4 sm:px-8 lg:px-14 py-10 grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-4 text-center">
            <div>
                <p class="font-display text-3xl sm:text-4xl font-semibold">40+</p>
                <p class="text-amber-200/90 text-sm mt-1">Daily varieties</p>
            </div>
            <div>
                <p class="font-display text-3xl sm:text-4xl font-semibold">6am</p>
                <p class="text-amber-200/90 text-sm mt-1">Doors open</p>
            </div>
            <div>
                <p class="font-display text-3xl sm:text-4xl font-semibold">100%</p>
                <p class="text-amber-200/90 text-sm mt-1">Real butter</p>
            </div>
            <div class="col-span-2 lg:col-span-1">
                <p class="font-display text-3xl sm:text-4xl font-semibold">4.9★</p>
                <p class="text-amber-200/90 text-sm mt-1">Happy neighbors</p>
            </div>
        </div>
    </section>

    <section id="treats" class="w-full py-16 sm:py-24">
        <div class="w-full px-4 sm:px-8 lg:px-14">
            <div class="mb-12">
                <h2 class="font-display text-3xl sm:text-4xl lg:text-5xl font-semibold text-amber-950">Fan favorites</h2>
                <p class="text-stone-600 mt-3 text-lg max-w-xl">Pulled from the oven before sunrise. Grab them while they're still warm.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                @php
                    $treats = [
                        ['name' => 'Country Sourdough', 'desc' => '72-hour ferment, crackly crust', 'price' => '$8', 'img' => 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=600&q=80', 'emoji' => '🍞'],
                        ['name' => 'Butter Croissant', 'desc' => 'Flaky layers, European butter', 'price' => '$4', 'img' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600&q=80', 'emoji' => '🥐'],
                        ['name' => 'Cinnamon Roll', 'desc' => 'Cream cheese frosting, soft center', 'price' => '$5', 'img' => 'https://images.unsplash.com/photo-1603532648955-039310f9a6a0?w=600&q=80', 'emoji' => '🌀'],
                        ['name' => 'Berry Tart', 'desc' => 'Seasonal fruit, vanilla custard', 'price' => '$7', 'img' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=600&q=80', 'emoji' => '🫐'],
                    ];
                @endphp
                @foreach ($treats as $treat)
                    <article class="group relative overflow-hidden rounded-2xl bg-white shadow-md shadow-amber-900/5 ring-1 ring-amber-100">
                        <div class="aspect-[4/3] overflow-hidden">
                            <img src="{{ $treat['img'] }}" alt="{{ $treat['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-lg" aria-hidden="true">{{ $treat['emoji'] }}</span>
                                    <h3 class="font-display text-lg font-semibold text-amber-950 mt-1">{{ $treat['name'] }}</h3>
                                    <p class="text-stone-500 text-sm mt-1">{{ $treat['desc'] }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-900">{{ $treat['price'] }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="about" class="w-full bg-amber-950 text-amber-50">
        <div class="w-full grid lg:grid-cols-2 min-h-[28rem]">
            <div class="relative min-h-[20rem] lg:min-h-full">
                <img
                    src="https://images.unsplash.com/photo-1486427944299-d1955d23a34e?w=1200&q=80"
                    alt="Baker kneading dough"
                    class="absolute inset-0 w-full h-full object-cover"
                >
            </div>
            <div class="flex flex-col justify-center px-4 sm:px-8 lg:px-14 py-14 lg:py-20">
                <h2 class="font-display text-3xl sm:text-4xl font-semibold mb-6">From our oven to your table</h2>
                <p class="text-amber-100/85 text-lg leading-relaxed mb-6">
                    Golden Crumb started in a tiny home kitchen with one sourdough starter named Gerald. Today we still mix every batch by hand, use local flour, and never rush the rise.
                </p>
                <p class="text-amber-100/85 text-lg leading-relaxed mb-8">
                    Whether you're picking up a Saturday loaf or ordering a birthday cake, you're part of the neighborhood table.
                </p>
                <ul class="space-y-3 text-amber-100/90">
                    <li class="flex items-center gap-3"><span class="text-amber-400">✓</span> Organic flour & free-range eggs</li>
                    <li class="flex items-center gap-3"><span class="text-amber-400">✓</span> No artificial preservatives</li>
                    <li class="flex items-center gap-3"><span class="text-amber-400">✓</span> Custom orders welcome</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="w-full py-16 sm:py-20 bg-[#f5ebe0]">
        <div class="w-full px-4 sm:px-8 lg:px-14">
            <h2 class="font-display text-3xl sm:text-4xl font-semibold text-amber-950 text-center mb-12">What neighbors say</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $reviews = [
                        ['quote' => 'Best croissants outside of Paris. I stop every Friday without fail.', 'name' => 'Maya T.', 'role' => 'Regular since 2019'],
                        ['quote' => 'They made our wedding cake — three tiers of lemon lavender dreams.', 'name' => 'James & Lin', 'role' => 'Wedding clients'],
                        ['quote' => 'My kids fight over the cinnamon rolls. Worth every penny.', 'name' => 'Priya K.', 'role' => 'Saturday regular'],
                    ];
                @endphp
                @foreach ($reviews as $review)
                    <blockquote class="rounded-2xl bg-white p-6 sm:p-8 shadow-sm ring-1 ring-amber-100">
                        <p class="text-stone-600 leading-relaxed">"{{ $review['quote'] }}"</p>
                        <footer class="mt-6">
                            <cite class="not-italic font-semibold text-amber-950">{{ $review['name'] }}</cite>
                            <p class="text-sm text-stone-500">{{ $review['role'] }}</p>
                        </footer>
                    </blockquote>
                @endforeach
            </div>
        </div>
    </section>

    <section id="order" class="w-full py-16 sm:py-24 bg-gradient-to-br from-amber-100 via-[#faf6f0] to-amber-50">
        <div class="w-full px-4 sm:px-8 lg:px-14">
            <div class="w-full rounded-3xl bg-amber-800 px-6 sm:px-12 lg:px-16 py-12 sm:py-16 lg:py-20 text-center lg:text-left lg:flex lg:items-center lg:justify-between lg:gap-12">
                <div class="lg:max-w-xl">
                    <h2 class="font-display text-3xl sm:text-4xl lg:text-5xl font-semibold text-amber-50 mb-4">Ready for something fresh?</h2>
                    <p class="text-amber-100/90 text-lg">Pre-order for pickup or swing by before we sell out. Open daily 6am – 3pm.</p>
                </div>
                <form class="mt-8 lg:mt-0 flex flex-col sm:flex-row gap-3 w-full lg:w-auto lg:min-w-[28rem]" action="#" method="get">
                    <label for="email" class="sr-only">Email address</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        placeholder="you@email.com"
                        class="flex-1 rounded-full border-0 px-6 py-4 text-stone-800 placeholder:text-stone-400 focus:ring-2 focus:ring-amber-300 outline-none"
                    >
                    <button type="submit" class="rounded-full bg-amber-950 px-8 py-4 font-semibold text-amber-50 hover:bg-black transition-colors whitespace-nowrap">
                        Get updates
                    </button>
                </form>
            </div>
        </div>
    </section>

    <footer class="w-full bg-amber-950 text-amber-200/80">
        <div class="w-full px-4 sm:px-8 lg:px-14 py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
            <div>
                <p class="flex items-center gap-2 font-display text-xl font-semibold text-amber-50">
                    <span aria-hidden="true">🥐</span> Golden Crumb
                </p>
                <p class="mt-3 text-sm leading-relaxed">123 Baker Street<br>Open daily 6am – 3pm</p>
            </div>
            <div>
                <p class="font-semibold text-amber-50 mb-3">Visit</p>
                <ul class="space-y-2 text-sm">
                    <li>Mon – Fri: 6am – 3pm</li>
                    <li>Sat – Sun: 7am – 4pm</li>
                </ul>
            </div>
            <div>
                <p class="font-semibold text-amber-50 mb-3">Connect</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-amber-50 transition-colors">Instagram</a></li>
                    <li><a href="#" class="hover:text-amber-50 transition-colors">Facebook</a></li>
                    <li><a href="tel:+15551234567" class="hover:text-amber-50 transition-colors">(555) 123-4567</a></li>
                </ul>
            </div>
        </div>
        <div class="w-full border-t border-amber-800 px-4 sm:px-8 lg:px-14 py-6 text-center text-sm text-amber-300/60">
            &copy; {{ date('Y') }} Golden Crumb Bakery. Made with butter and joy.
        </div>
        <div class="w-full px-4 pb-5 pt-1 text-center">
            <a
                href="{{ route('admin.login') }}"
                class="text-[10px] sm:text-[11px] text-amber-400/25 hover:text-amber-400/45 transition-colors"
            >Staff Login</a>
        </div>
    </footer>

</body>
</html>
