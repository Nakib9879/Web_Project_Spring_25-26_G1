<?php
/** @var array $stats
 * @var array $chartDates
 * @var array $chartTotals
 */
include 'views/layout/header.php';
?>
    <div class="admin-dashboard-container">
        <h2>Admin Dashboard</h2>

        <div class="grid-cards" style="margin-bottom: 30px;">
            <div class="card" style="text-align: center;">
                <h3>Total Categories</h3>
                <h1 style="font-size: 48px; margin: 10px 0; color: var(--primary);"><?php echo $stats['total_categories']; ?></h1>
                <a href="index.php?action=admin_categories" class="btn">Manage Categories</a>
            </div>

            <div class="card" style="text-align: center;">
                <h3>Menu Items</h3>
                <h1 style="font-size: 42px; margin: 10px 0; color: var(--primary);"><?php echo $stats['total_items']; ?></h1>
                <a href="index.php?action=admin_menu" class="btn" style="width: 100%;">Manage Menu</a>
            </div>

            <div class="card" style="text-align: center;">
                <h3>Inactive Items</h3>
                <h1 style="font-size: 42px; margin: 10px 0; color: var(--danger);"><?php echo $stats['inactive_items']; ?></h1>
                <a href="index.php?action=admin_menu" class="btn" style="width: 100%; background: var(--danger);">Review Menu</a>
            </div>
        </div>

        <div class="card" style="padding: 30px;">
            <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary);">7-Day Sales Trend</h3>
            <canvas id="salesChart" height="80"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');

        const dates = <?php echo json_encode($chartDates); ?>;
        const totals = <?php echo json_encode($chartTotals); ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Daily Revenue ($)',
                    data: totals,
                    borderColor: '#e67e22',
                    backgroundColor: 'rgba(230, 126, 34, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#2c3e50',
                    pointRadius: 5,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: function(value) { return '$' + value; } }
                    }
                }
            }
        });
    </script>
<?php include 'views/layout/footer.php'; ?>