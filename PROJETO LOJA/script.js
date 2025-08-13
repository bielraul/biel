// Dados dos produtos
        const products = [
            {
                id: 1,
                name: "Blazer Elegante",
                price: 299.90,
                image: "https://placehold.co/300x400/000000/FFFFFF?text=Blazer+Elegante"
            },
            {
                id: 2,
                name: "Vestido Vermelho",
                price: 199.90,
                image: "https://placehold.co/300x400/FF0000/FFFFFF?text=Vestido+Vermelho"
            },
            {
                id: 3,
                name: "Camisa Social Preta",
                price: 159.90,
                image: "https://placehold.co/300x400/000000/FFFFFF?text=Camisa+Social"
            },
            {
                id: 4,
                name: "Calça Jeans Slim",
                price: 179.90,
                image: "https://placehold.co/300x400/000000/FFFFFF?text=Calca+Jeans"
            },
            {
                id: 5,
                name: "Terno Completo",
                price: 499.90,
                image: "https://placehold.co/300x400/000000/FFFFFF?text=Terno+Completo"
            },
            {
                id: 6,
                name: "Saia Midi",
                price: 129.90,
                image: "https://placehold.co/300x400/000000/FFFFFF?text=Saia+Midi"
            }
        ];

        // Estado do carrinho
        let cart = [];

        // Elementos do DOM
        const productsGrid = document.getElementById('productsGrid');
        const cartButton = document.getElementById('cartButton');
        const cartSidebar = document.getElementById('cartSidebar');
        const closeCart = document.getElementById('closeCart');
        const cartItems = document.getElementById('cartItems');
        const cartCount = document.getElementById('cartCount');
        const cartTotal = document.getElementById('cartTotal');
        const overlay = document.getElementById('overlay');

        // Renderizar produtos
        function renderProducts() {
            productsGrid.innerHTML = '';
            
            products.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'product-card';
                productCard.innerHTML = `
                    <img src="${product.image}" alt="${product.name}" class="product-image">
                    <div class="product-info">
                        <h3 class="product-title">${product.name}</h3>
                        <p class="product-price">R$ ${product.price.toFixed(2)}</p>
                        <button class="add-to-cart" data-id="${product.id}">Adicionar ao Carrinho</button>
                    </div>
                `;
                
                productsGrid.appendChild(productCard);
            });
            
            // Adicionar event listeners aos botões
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', addToCart);
            });
        }

        // Adicionar ao carrinho
        function addToCart(event) {
            const productId = parseInt(event.target.getAttribute('data-id'));
            const product = products.find(p => p.id === productId);
            
            // Verificar se o produto já está no carrinho
            const existingItem = cart.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    quantity: 1
                });
            }
            
            updateCart();
        }

        // Remover item do carrinho
        function removeFromCart(productId) {
            cart = cart.filter(item => item.id !== productId);
            updateCart();
        }

        // Atualizar carrinho
        function updateCart() {
            // Atualizar contador
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
            
            // Atualizar sidebar
            cartItems.innerHTML = '';
            
            if (cart.length === 0) {
                cartItems.innerHTML = '<p>Seu carrinho está vazio</p>';
                cartTotal.textContent = '0.00';
                return;
            }
            
            let total = 0;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <img src="${item.image}" alt="${item.name}" class="cart-item-img">
                    <div class="cart-item-details">
                        <h4 class="cart-item-title">${item.name}</h4>
                        <p class="cart-item-price">R$ ${item.price.toFixed(2)} x ${item.quantity}</p>
                        <button class="remove-item" data-id="${item.id}">Remover</button>
                    </div>
                `;
                
                cartItems.appendChild(cartItem);
            });
            
            // Adicionar event listeners aos botões de remover
            document.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', () => {
                    removeFromCart(parseInt(button.getAttribute('data-id')));
                });
            });
            
            // Atualizar total
            cartTotal.textContent = total.toFixed(2);
        }

        // Event listeners
        cartButton.addEventListener('click', () => {
            cartSidebar.classList.add('active');
            overlay.classList.add('active');
        });

        closeCart.addEventListener('click', () => {
            cartSidebar.classList.remove('active');
            overlay.classList.remove('active');
        });

        overlay.addEventListener('click', () => {
            cartSidebar.classList.remove('active');
            overlay.classList.remove('active');
        });

        // Inicializar a loja
        renderProducts();
        updateCart();