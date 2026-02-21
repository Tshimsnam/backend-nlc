<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Grand Salon de l'Autisme - 15-16 Avril 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .hero-pattern {
            background-color: #1e3a8a;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .7;
            }
        }
        
        .slide-in {
            animation: slideIn 0.8s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Hero Section -->
    <section class="hero-pattern min-h-screen flex items-center justify-center relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute top-10 left-10 w-20 h-20 bg-yellow-400 rounded-full opacity-20 pulse-animation"></div>
        <div class="absolute bottom-20 right-20 w-32 h-32 bg-blue-300 rounded-full opacity-20 pulse-animation" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-purple-400 rounded-full opacity-20 pulse-animation" style="animation-delay: 0.5s;"></div>
        
        <div class="container mx-auto px-4 py-16 relative z-10">
            <div class="max-w-5xl mx-auto">
                <!-- Logo NLC -->
                <div class="text-center mb-8 slide-in">
                    <div class="inline-block bg-white rounded-2xl p-6 shadow-2xl">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-2xl">NLC</span>
                            </div>
                            <div class="text-left">
                                <h3 class="text-xl font-bold text-gray-900">Never Limit Children</h3>
                                <p class="text-sm text-gray-600">Ensemble pour l'inclusion</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Title -->
                <div class="text-center mb-12 slide-in" style="animation-delay: 0.2s;">
                    <div class="inline-block bg-yellow-400 px-6 py-2 rounded-full mb-6">
                        <span class="text-blue-900 font-bold text-lg">Le</span>
                    </div>
                    <h1 class="text-6xl md:text-8xl font-extrabold text-white mb-4 leading-tight">
                        Grand<br>
                        Salon de<br>
                        <span class="text-yellow-400">L'AUTISME</span>
                    </h1>
                    <div class="w-32 h-2 bg-yellow-400 mx-auto rounded-full"></div>
                </div>

                <!-- Event Details -->
                <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-12 mb-8 slide-in" style="animation-delay: 0.4s;">
                    <div class="grid md:grid-cols-2 gap-8 mb-8">
                        <!-- Date & Time -->
                        <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl p-6 text-center">
                            <div class="text-blue-900 font-bold text-5xl mb-2">15 › 16</div>
                            <div class="text-blue-900 font-bold text-3xl mb-2">Avril</div>
                            <div class="text-blue-900 font-bold text-4xl">2026</div>
                            <div class="mt-4 pt-4 border-t-2 border-blue-900">
                                <div class="text-blue-900 font-bold text-2xl">08H - 16H</div>
                            </div>
                        </div>

                        <!-- Location & Contact -->
                        <div class="space-y-4">
                            <div class="bg-blue-50 rounded-xl p-4 flex items-start gap-3">
                                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-blue-900 text-lg">Fleuve Congo Hôtel</div>
                                    <div class="text-blue-700">Kinshasa</div>
                                </div>
                            </div>

                            <div class="bg-blue-50 rounded-xl p-4 flex items-start gap-3">
                                <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900">Infoline</div>
                                    <a href="tel:+243844338747" class="text-green-600 hover:text-green-700 font-semibold">+243 844 338 747</a>
                                </div>
                            </div>

                            <div class="bg-blue-50 rounded-xl p-4 flex items-start gap-3">
                                <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900">Email</div>
                                    <a href="mailto:info@nlcrdc.org" class="text-purple-600 hover:text-purple-700 font-semibold">info@nlcrdc.org</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">À Propos de l'Événement</h2>
                        <p class="text-gray-700 text-lg leading-relaxed mb-4">
                            Rejoignez-nous pour deux jours de conférences, d'ateliers pratiques et d'échanges enrichissants sur le trouble du spectre autistique et son impact sur la scolarité.
                        </p>
                        <p class="text-gray-700 text-lg leading-relaxed">
                            Cet événement rassemble des professionnels de la santé, des éducateurs, des parents et des étudiants pour partager des connaissances, des expériences et des solutions concrètes pour une meilleure inclusion.
                        </p>
                    </div>

                    <!-- CTA Button -->
                    <div class="text-center">
                        <a href="{{ env('FRONTEND_WEBSITE_URL', 'http://localhost:8080') }}/evenements" class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold text-xl px-12 py-5 rounded-full shadow-2xl transform hover:scale-105 transition-all duration-300">
                            <span class="flex items-center gap-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                </svg>
                                Je m'inscris
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </span>
                        </a>
                        <p class="text-gray-600 mt-4 text-sm">
                            Date limite d'inscription : <span class="font-bold text-red-600">10 Avril 2026</span>
                        </p>
                    </div>
                </div>

                <!-- Program Highlights -->
                <div class="grid md:grid-cols-2 gap-6 mb-8 slide-in" style="animation-delay: 0.6s;">
                    <!-- Day 1 -->
                    <div class="bg-white rounded-2xl p-6 shadow-xl">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-xl">1</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-xl text-gray-900">Jour 1</h3>
                                <p class="text-gray-600">15 Avril 2026</p>
                            </div>
                        </div>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Conférences plénières</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Ateliers pratiques</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Sessions de networking</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Day 2 -->
                    <div class="bg-white rounded-2xl p-6 shadow-xl">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-xl">2</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-xl text-gray-900">Jour 2</h3>
                                <p class="text-gray-600">16 Avril 2026</p>
                            </div>
                        </div>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Ateliers spécialisés</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Études de cas</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Table ronde et clôture</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Sponsors -->
                <div class="bg-white rounded-2xl p-8 shadow-xl slide-in" style="animation-delay: 0.8s;">
                    <h2 class="text-2xl font-bold text-gray-900 text-center mb-6">Nos Partenaires</h2>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-6 items-center justify-items-center">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mx-auto mb-2">
                                <span class="font-bold text-blue-900 text-xs">AGEPE</span>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="w-20 h-20 bg-gradient-to-br from-green-100 to-green-200 rounded-full flex items-center justify-center mx-auto mb-2">
                                <span class="font-bold text-green-900 text-xs">SOFIBANQUE</span>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="w-20 h-20 bg-gradient-to-br from-purple-100 to-purple-200 rounded-full flex items-center justify-center mx-auto mb-2">
                                <span class="font-bold text-purple-900 text-xs">TIJE</span>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="w-20 h-20 bg-gradient-to-br from-pink-100 to-pink-200 rounded-full flex items-center justify-center mx-auto mb-2">
                                <span class="font-bold text-pink-900 text-xs">Vodacom</span>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="w-20 h-20 bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-full flex items-center justify-center mx-auto mb-2">
                                <span class="font-bold text-yellow-900 text-xs">Ecobank</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-center text-gray-600 mt-6 text-sm">
                        Et 5 autres partenaires prestigieux
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400 mb-2">© 2026 Never Limit Children. Tous droits réservés.</p>
            <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
                <a href="mailto:info@nlcrdc.org" class="hover:text-white transition">Contact</a>
                <span>•</span>
                <a href="tel:+243844338747" class="hover:text-white transition">+243 844 338 747</a>
            </div>
        </div>
    </footer>
</body>
</html>
