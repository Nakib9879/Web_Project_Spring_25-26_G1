<?php include 'views/layout/header.php'; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="margin: 0;">Menu</h2>
    </div>

    <div class="card" style="display: flex; gap: 15px; flex-wrap: wrap; padding: 15px; align-items: center; margin-bottom: 30px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">

        <div style="flex: 2; min-width: 200px;">
            <input type="text" id="filter-q" placeholder="Search burgers, pizzas, etc..." oninput="triggerFilter()" style="margin: 0;">
        </div>

        <div style="flex: 1; min-width: 150px;">
            <select id="filter-cat" onchange="triggerFilter()" style="margin: 0; cursor: pointer;">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="grid-cards" id="menu-container">
        <?php foreach($items as $item): ?>
            <div class="card item-card" style="display: flex; flex-direction: column;">
                <img src="public/uploads/menu/<?php echo $item['image_path']; ?>" alt="Food" style="width:100%; height:180px; object-fit:cover; border-radius:4px; margin-bottom: 15px;">
                <h3 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($item['name']); ?></h3>
                <p style="color: #666; font-size: 13px; flex-grow: 1; margin: 0 0 15px 0; line-height: 1.4;"><?php echo htmlspecialchars($item['description']); ?></p>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <span style="font-size: 18px; font-weight: bold; color: var(--primary);">$<?php echo number_format($item['price'], 2); ?></span>
                </div>
                <button onclick="addToCart(<?php echo $item['id']; ?>, <?php echo $item['price']; ?>)" class="btn" style="width:100%; font-weight: bold;">Add to Cart</button>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="toast" style="visibility: hidden; min-width: 250px; background-color: var(--success); color: #fff; text-align: center; border-radius: 4px; padding: 16px; position: fixed; z-index: 1; right: 30px; bottom: 30px; font-weight: bold; box-shadow: 0 4px 8px rgba(0,0,0,0.2); transition: opacity 0.3s, visibility 0.3s; opacity: 0;">
        Item added to cart!
    </div>

    <script>
        function triggerFilter() {
            const q = document.getElementById('filter-q').value;
            const cat = document.getElementById('filter-cat').value;

            const params = new URLSearchParams({
                q: q,
                category: cat
            });

            fetch('index.php?action=api/menu-items/search&' + params.toString())
                .then(response => response.json())
                .then(items => {
                    const container = document.getElementById('menu-container');
                    container.innerHTML = ''; // Clear current grid

                    if(items.length === 0) {
                        container.innerHTML = `
                            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #7f8c8d;">
                                <h3>No items found</h3>
                                <p>Try adjusting your filters or search term.</p>
                            </div>`;
                        return;
                    }

                    items.forEach(item => {
                        container.innerHTML += `
                        <div class="card item-card" style="display: flex; flex-direction: column;">
                            <img src="public/uploads/menu/${item.image_path}" alt="Food" style="width:100%; height:180px; object-fit:cover; border-radius:4px; margin-bottom: 15px;" onerror="this.src='public/uploads/menu/default.png'">
                            <h3 style="margin: 0 0 5px 0;">${item.name}</h3>
                            <p style="color: #666; font-size: 13px; flex-grow: 1; margin: 0 0 15px 0; line-height: 1.4;">${item.description || ''}</p>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <span style="font-size: 18px; font-weight: bold; color: var(--primary);">$${parseFloat(item.price).toFixed(2)}</span>
                            </div>
                            <button onclick="addToCart(${item.id}, ${item.price})" class="btn" style="width:100%; font-weight: bold;">Add to Cart</button>
                        </div>`;
                    });
                });
        }

        function addToCart(itemId, price) {
            let formData = new FormData();
            formData.append('item_id', itemId);
            formData.append('price', price);

            fetch('index.php?action=api/cart/add', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        document.getElementById('cart-count').innerText = data.total_cart_count;

                        let toast = document.getElementById("toast");
                        toast.style.visibility = "visible";
                        toast.style.opacity = "1";
                        setTimeout(function(){
                            toast.style.opacity = "0";
                            setTimeout(() => toast.style.visibility = "hidden", 300);
                        }, 2500);
                    }
                });
        }
    </script>
<?php include 'views/layout/footer.php'; ?>