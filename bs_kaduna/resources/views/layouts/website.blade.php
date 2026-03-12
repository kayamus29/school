<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'School Website'))</title>
    <meta name="description" content="@yield('meta_description', 'Welcome to our school website.')">

    <!-- CSS (Using Bootstrap 5 for modern feel) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .hero-banner {
            height: 60vh;
            min-height: 400px;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .footer {
            background: #212529;
            color: white;
            padding: 3rem 0;
        }

        .card {
            border: none;
            transition: transform 0.2s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .btn-primary {
            padding: 0.6rem 1.5rem;
            font-weight: 600;
        }

        img.featured-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
    </style>
    @yield('styles')
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand" href="{{ route('website.home') }}">
                <i class="bi bi-mortarboard-fill text-primary"></i>
                {{ config('app.name', 'Unifiedtransform') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('website.home') ? 'active' : '' }}"
                            href="{{ route('website.home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('website.blog*') ? 'active' : '' }}"
                            href="{{ route('website.blog') }}">News & Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('website.contact') ? 'active' : '' }}"
                            href="{{ route('website.contact') }}">Contact</a>
                    </li>
                    @guest
                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-outline-primary" href="{{ route('login') }}">Login Portal</a>
                        </li>
                    @else
                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-primary" href="{{ route('home') }}">Dashboard</a>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>About Our School</h5>
                    <p class="text-muted">Dedicated to academic excellence and character building. Empowering the next
                        generation of leaders.</p>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('website.home') }}" class="text-white-50 text-decoration-none">Home</a>
                        </li>
                        <li><a href="{{ route('website.blog') }}" class="text-white-50 text-decoration-none">Latest
                                News</a></li>
                        <li><a href="{{ route('website.contact') }}" class="text-white-50 text-decoration-none">Contact
                                Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Contact Info</h5>
                    <p class="text-muted mb-1"><i class="bi bi-geo-alt me-2"></i> 123 Education Way, Academic City</p>
                    <p class="text-muted mb-1"><i class="bi bi-envelope me-2"></i> info@school.edu</p>
                    <p class="text-muted"><i class="bi bi-telephone me-2"></i> +1 234 567 890</p>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Follow Us</h5>
                    <div class="d-flex gap-3 fs-4">
                        <a href="#" class="text-white-50"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-4 bg-secondary">
            <div class="text-center text-muted small">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>

</html>
