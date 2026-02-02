<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMB Link - Smart Life Easy Life</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .text-shadow-md {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4); 
        }
        
        /* Custom size for the logo image in header/footer - SIGNIFICANTLY INCREASED */
       

        .logo-img {
            height: 300px; /* Increased Height */
            width: auto;
            max-width: 500px; /* Increased Max Width on small screens */
            object-fit: contain; 
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <header class="bg-white shadow-md p-4 sticky top-0 z-50">
        <nav class="container mx-auto flex justify-between items-center">
            <a href="#" class="text-2xl font-bold text-gray-900">
                <img src="https://github.com/MMB-LINK/mmblink/blob/main/mmb%20link%20lgo.jpeg?raw=true" alt="MMB Link Logo" class="logo-img">
            </a>
            <div class="hidden md:flex space-x-6">
                <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">Home</a>
                <a href="#products" class="text-gray-600 hover:text-indigo-600 transition-colors">Products</a>
                <a href="#about" class="text-gray-600 hover:text-indigo-600 transition-colors">About</a>
                <a href="#contact" class="text-gray-600 hover:text-indigo-600 transition-colors">Contact</a>
            </div>
            <button id="mobile-menu-button" class="md:hidden text-gray-600 hover:text-gray-900 transition-colors focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </nav>
        <div id="mobile-menu" class="hidden md:hidden mt-4 space-y-2">
            <a href="#" class="block px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-md transition-colors">Home</a>
            <a href="#products" class="block px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-md transition-colors">Products</a>
            <a href="#about" class="block px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-md transition-colors">About</a>
            <a href="#contact" class="block px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-md transition-colors">Contact</a>
        </div>
    </header>

    <main>
        <section class="bg-gray-900 text-white py-20 md:py-32 px-4">
            <div class="container mx-auto text-center flex flex-col items-center">
                
                <h1 class="text-4xl md:text-6xl font-extrabold text-white">Smart Life Easy Life</h1>
                <p class="mt-4 text-lg md:text-xl font-light">Innovative solutions for a sustainable future.</p>
                <a href="#products" class="mt-8 inline-block bg-white text-gray-900 font-bold py-3 px-8 rounded-full shadow-lg hover:bg-gray-200 transition-colors transform hover:scale-105">Discover Products</a>
            </div>
        </section>

        <section id="about" class="container mx-auto py-12 px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">Our Mission & Vision</h2>
                    <p class="text-gray-600 leading-relaxed">
                        To make sustainable living simple, affordable, and stylish for every household. We are geared towards building a better, more sustainable life for millions around the world. Our vision is to create innovative solutions that only serve today’s needs but also preserve the future for generations to come. With every product we design, MMB Link is committed to driving the future toward a smart, easy life.
                    </p>
                </div>
                <div>
                    <img src="https://placehold.co/600x400/cccccc/000000?text=Sustainable+Energy" alt="Sustainable Living" class="w-full h-auto rounded-xl shadow-lg">
                </div>
            </div>
        </section>
        
        <hr class="border-gray-300 mx-auto max-w-7xl">

        <section id="products" class="container mx-auto py-12 px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-10">Our Products</h2>

            <div class="mb-12">
                <h3 class="text-2xl font-semibold mb-6 border-b-2 border-gray-300 pb-2">Solar Gadgets</h3>
                <div id="solar-products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                </div>
            </div>
            
            <hr class="border-gray-200 my-8">

            <div>
                <h3 class="text-2xl font-semibold mb-6 border-b-2 border-gray-300 pb-2">Bikes</h3>
                <div id="bike-products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                </div>
            </div>

        </section>

        <section class="bg-white py-12 px-4">
            <div class="container mx-auto text-center">
                <h2 class="text-3xl font-bold mb-4">What Our Customers Say</h2>
                <div class="max-w-xl mx-auto">
                    <p class="text-gray-600 italic">"The solar power bank is a game-changer! It's so reliable and easy to use. I love knowing I'm using clean energy to power my devices."</p>
                    <p class="font-semibold mt-4 text-gray-900">- A Satisfied Customer</p>
                </div>
            </div>
        </section>

        </main>

    <div id="image-modal" class="fixed inset-0 bg-black bg-opacity-80 z-50 flex items-center justify-center p-4 hidden">
        <div class="relative max-w-4xl max-h-[90vh]">
            <button id="close-modal-btn" class="absolute top-4 right-4 text-white text-3xl font-bold hover:text-gray-300 transition-colors">
                &times;
            </button>
            <img id="modal-image" src="" alt="Full-size product image" class="rounded-xl shadow-lg max-w-full max-h-[85vh] object-contain">
        </div>
    </div>

    <footer id="contact" class="bg-black text-gray-300 py-12">
        <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
            <div>
                <h3 class="text-xl font-bold text-white mb-4">
                    <img src="https://github.com/MMB-LINK/mmblink/blob/main/mmb%20link%20lgo.jpeg?raw=true" alt="MMB Link Logo" class="logo-img mx-auto md:mx-0 mb-2">
                </h3>
                <p class="text-sm">Driven toward a smart, easy life with innovative, sustainable solutions.</p>
                <div class="flex justify-center md:justify-start space-x-4 mt-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">Facebook</a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">Instagram</a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">TikTok</a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">X (Twitter)</a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">LinkedIn</a>
                </div>
            </div>
            <div>
                <h3 class="text-xl font-bold text-white mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Terms & Conditions</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Refund Policy</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-xl font-bold text-white mb-4">Contact Us</h3>
                <p class="text-sm">Phone: <a href="tel:+2349071198792" class="hover:text-white transition-colors">+234 907 119 8792</a></p>
                <p class="text-sm mt-2">WhatsApp: <a href="https://wa.me/2349071198792" class="hover:text-white transition-colors">+234 907 119 8792</a></p>
                <p class="text-sm mt-2">Email: <a href="mailto:mmblink.ng@gmail.com" class="hover:text-white transition-colors">mmblink.ng@gmail.com</a></p>
                <p class="text-sm mt-2">Address: Abuja, Nigeria </p>
            </div>
        </div>
        <div class="text-center mt-8 text-gray-500 text-sm">
            <p>&copy; 2025 MMB Link. All rights reserved. <br> **Worldwide delivery available.** Delivery fee to be finalized.</p>
        </div>
    </footer>

    <script type="module">
        const WHATSAPP_PHONE_NUMBER = '2349071198792';

        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        // Image modal elements
        const imageModal = document.getElementById('image-modal');
        const modalImage = document.getElementById('modal-image');
        const closeModalBtn = document.getElementById('close-modal-btn');

        // PRODUCT DATA with all prices now set to 9999
        const products = {
            'solar': [
                { id: 'solar-fan', name: 'Super Power mmb • link Solar Fan', price: 9999, description: 'A powerful, quiet, and oscillating fan with USB and LED light features. This AC/DC fan is equipped with a 3-speed adjustment, automatic left-to-right movement, and a gross weight of 6kg. Its dimensions are 144.5X15.3X37 cm.', image: 'https://raw.githubusercontent.com/RECRUIT-CONNECT-NIGLTD/mmblink/main/Model%20name%20Super%20Power%20mmb%20%E2%80%A2%20link%20Solar%20Fan.jpeg' },
                { id: 'solar-generator', name: 'Solar Power Generator', price: 9999, description: 'A powerful portable solar power generator featuring a 500W sine wave power supply and a 512 watt-hour LiFePO40312.8 battery, with a maximum discharge power of 600W. It\'s designed to provide reliable, clean energy for your devices.', image: 'https://raw.githubusercontent.com/RECRUIT-CONNECT-NIGLTD/mmblink/main/Solar%20power%20generator%20500W%20sine%20wave%20512%20watt-hour%20battery.jpeg' },
                { id: 'solar-generator-400w', name: 'Solar Power Generator (400W)', price: 9999, description: 'A solar power generator with a 400W power supply and a 384-hour, Iron Phosphate 30A 12.8V battery. It has a maximum discharge power of 400W.', image: 'https://raw.githubusercontent.com/RECRUIT-CONNECT-NIGLTD/mmblink/main/Solar%20power%20generator%20400W%20power%20supply%20384%20hours%20Battery.jpeg' },
                { id: 'lithium-battery', name: '16.5 Kilowatt Lithium Battery', price: 9999, description: 'A high-capacity lithium battery with a touch screen control and a storage capacity of 16500Wh. It has a nominal voltage of 51.2VDC, an output of 10000W, and can be paralleled with up to 10 units for expanded power needs. It features RS485/CAN communication interfaces and is NSDS/UN38.3 certified.', image: 'https://raw.githubusercontent.com/RECRUIT-CONNECT-NIGLTD/mmblink/main/165%20kilowatts%20lithium%20battery.jpeg' },
                { id: '10kw-inverter', name: '10KW Inverter', price: 9999, description: 'A powerful 10KW inverter with a DC voltage of 48V. It features a maximum MPPT current of 160A and supports AC input/output of 220/230VAC. With a maximum PV input power of 10000W (5000W*2) and support for up to 6 parallel units, it\'s ideal for large-scale solar systems. It also includes RS232/RS485/dry contact communication interfaces and BMS communication.', image: 'https://raw.githubusercontent.com/RECRUIT-CONNECT-NIGLTD/mmblink/main/10kw%20inverter%20AC%20DW.jpeg' },
                { id: 'fan-cooler', name: 'Fan Cooler', price: 9999, description: 'A powerful and versatile fan cooler with 45W power. It features 80° left and right oscillation, 8 speed settings, and a 9-hour timer function. Dimensions are 82x15x15CM.', image: 'https://raw.githubusercontent.com/RECRUIT-CONNECT-NIGLTD/mmblink/main/Model%20fan%20cooler.jpeg' }
            ],
            'bikes': [
                { id: 'd100-ebike', name: 'D100 Electric Bike', price: 9999, description: 'A powerful electric bike with a 1000W motor, 60V20AH battery, and a top speed of 40-50 km/h. Features include a speedometer LED, remote control, anti-theft alarm, and cruise control.', image: 'https://raw.githubusercontent.com/RECRUIT-CONNECT-NIGLTD/mmblink/main/Bike%20name%20D100.jpeg' },
                { id: 'd11-gt-ebike', name: 'D11 GT Electric Bike', price: 9999, description: 'A high-performance electric bike featuring a 1200W motor, 72V32AH lead acid battery, and dual disc brakes for enhanced safety. It boasts a max speed of 50-55 km/h and a range of 50-60 km.', image: 'https://raw.githubusercontent.com/RECRUIT-CONNECT-NIGLTD/mmblink/main/Bike%20name%20D11%20GT.jpeg' }
            ]
        };

        const allProducts = { 
            ...products.solar.reduce((acc, p) => ({ ...acc, [p.id]: p }), {}), 
            ...products.bikes.reduce((acc, p) => ({ ...acc, [p.id]: p }), {}) 
        };

        const renderProducts = (gridId, productsArray) => {
            const grid = document.getElementById(gridId);
            if (!grid) {
                console.error(`Grid element with id "${gridId}" not found.`);
                return;
            }
            grid.innerHTML = '';
            productsArray.forEach(product => {
                // Use a proper locale for Nigerian Naira formatting
                const priceText = `₦${product.price.toLocaleString('en-NG')}`; // All prices are 9999 now
                
                // Price color is always black now that all prices are confirmed
                const priceColorClass = 'text-gray-900';

                const productCard = `
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition-transform duration-300 hover:scale-105">
                        <img src="${product.image}" alt="${product.name}" class="w-full h-64 object-cover cursor-pointer product-image" data-full-image="${product.image}">
                        <div class="p-6">
                            <h4 class="text-xl font-semibold text-gray-900 mb-2">${product.name}</h4>
                            <p class="text-gray-600 mb-4 text-sm">${product.description}</p>
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-4 pt-4 border-t border-gray-100">
                                <span class="text-2xl font-bold ${priceColorClass} mb-2 sm:mb-0">${priceText}</span>
                                <button class="whatsapp-order-btn bg-gray-800 text-white font-semibold py-2 px-4 rounded-full hover:bg-gray-900 transition-colors text-sm" data-product-id="${product.id}">Order on WhatsApp</button>
                            </div>
                        </div>
                    </div>
                `;
                grid.innerHTML += productCard;
            });
        };
        
        const setupInteractions = () => {
             // 1. Setup Image Modal functionality
            document.querySelectorAll('.product-image').forEach(image => {
                image.addEventListener('click', () => {
                    const fullImageSrc = image.getAttribute('data-full-image');
                    modalImage.src = fullImageSrc;
                    imageModal.classList.remove('hidden');
                });
            });

            closeModalBtn.addEventListener('click', () => {
                imageModal.classList.add('hidden');
            });

            imageModal.addEventListener('click', (event) => {
                if (event.target === imageModal) {
                    imageModal.classList.add('hidden');
                }
            });

            // 2. Setup WhatsApp Buttons functionality
            document.querySelectorAll('.whatsapp-order-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const productId = button.dataset.productId;
                    const product = allProducts[productId];
                    if (product) {
                        // Message updated for uniform pricing
                        const priceMessage = `The listed price is ₦${product.price.toLocaleString('en-NG')}.`;
                        
                        const message = `Hello, I would like to order the product: *${product.name}*. ${priceMessage} Please confirm availability and the total cost.`;
                        const encodedMessage = encodeURIComponent(message);
                        window.location.href = `https://wa.me/${WHATSAPP_PHONE_NUMBER}?text=${encodedMessage}`;
                    } else {
                        console.error('Product not found for ID:', productId);
                    }
                });
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            // Mobile menu toggle
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });

            // Render products first
            renderProducts('solar-products-grid', products.solar);
            renderProducts('bike-products-grid', products.bikes);
            
            // Then, set up all dynamic interactions
            setupInteractions();
        });
    </script>
</body>
</html>
