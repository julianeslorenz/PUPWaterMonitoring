<?php
    // require __DIR__ . '/../water/authentication.php';

?>

<!DOCTYPE html>
<html>
<head>
  <title>Water Usage Charts</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div id="statistics" class="tab-content">
    <div class="card-container">
      <h2 style="text-align: center; margin-bottom: 20px; color: #333;">📊 Water Consumption Statistics</h2>
        <?php
          $defaultFrom = date('Y-m-d', strtotime('-7 days'));
          $defaultTo = date('Y-m-d');
        ?>
          <div class="controls">
            <label for="chart-type">Select Range:</label>
            <select id="chart-type" onchange="toggleChartView()">
              <option value="all" selected>Show All</option>
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
              <option value="yearly">Yearly</option>
            </select>

            From: <input type="date" id="date-from" value="<?= $defaultFrom ?>">
            To: <input type="date" id="date-to" value="<?= $defaultTo ?>">
            <button class="btn btn-secondary" onclick="loadSelectedChart()">Apply</button>
          </div>
          <div class="stats-grid">
              <!-- Daily -->
              <div class="chart-card chart-block" id="daily-block" style="display:none;">
                <h5 class="chart-title">Daily Usage</h5>
                <button class="btn btn-secondary" onclick="exportCSV('daily')">Export to CSV</button>
                <canvas id="dailyChart" height="300"></canvas>
              </div>
                <!-- Weekly -->
              <div class="chart-card chart-block" id="weekly-block" style="display:none;">
                <h5 class="chart-title">Weekly Usage</h5>
                <button class="btn btn-secondary" onclick="exportCSV('weekly')">Export to CSV</button>
                <canvas id="weeklyChart" height="300"></canvas>
              </div>
          </div>
          <div class="stats-grid">
            <!-- Monthly -->
              <div class="chart-card chart-block" id="monthly-block" style="display:none;">
                <h5 class="chart-title">Monthly Usage</h5>
                <button class="btn btn-secondary" onclick="exportCSV('monthly')">Export to CSV</button>
                <canvas id="monthlyChart" height="300"></canvas>
              </div>

              <!-- Yearly -->
              <div class="chart-card chart-block" id="yearly-block" style="display:none;">
                <h5 class="chart-title">Yearly Usage</h5>
                <button class="btn btn-secondary" onclick="exportCSV('yearly')">Export to CSV</button>
                <canvas id="yearlyChart" height="300"></canvas>
              </div>
            </div>
    </div>
</div>

<script>
  const chartInstances = {};

  async function loadChart(type) {
    const from = document.getElementById('date-from').value;
    const to = document.getElementById('date-to').value;

    const res = await fetch(`chart_data.php?type=${type}&from=${from}&to=${to}`);
    const data = await res.json();

    const labels = data.tank1?.labels || [];
    const tank1Data = data.tank1?.data || [];
    const tank2Data = data.tank2?.data || [];

    const ctx = document.getElementById(`${type}Chart`).getContext('2d');

    if (chartInstances[type]) chartInstances[type].destroy();

    chartInstances[type] = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          {
            label: 'Tank 1',
            data: tank1Data,
            backgroundColor: '#3498db'
          },
          {
            label: 'Tank 2',
            data: tank2Data,
            backgroundColor: '#e74c3c'
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: `${type.charAt(0).toUpperCase() + type.slice(1)} Water Usage`
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Liters'
            }
          }
        }
      }
    });
  }
function loadSelectedChart() {
  const type = document.getElementById('chart-type').value;

  ['daily', 'weekly', 'monthly', 'yearly'].forEach(t => {
    const block = document.getElementById(`${t}-block`);
    const canvas = document.getElementById(`${t}Chart`);

    if (type === 'all') {
      block.style.display = 'block';
      canvas.classList.remove('fixed-size'); // remove fixed height in "show all"
      loadChart(t);
    } else {
      if (t === type) {
        block.style.display = 'block';
        canvas.classList.add('fixed-size'); // apply fixed height when filtered
        loadChart(t);
      } else {
        block.style.display = 'none';
        canvas.classList.remove('fixed-size');
      }
    }
  });
}
  function toggleChartView() {
    loadSelectedChart();
  }

async function exportCSV(type) {
  const from = document.getElementById('date-from').value;
  const to = document.getElementById('date-to').value;

  const res = await fetch(`chart_data.php?type=${type}&from=${from}&to=${to}`);
  const data = await res.json();

  const labels = data.tank1?.labels || [];
  const tank1Data = data.tank1?.data || [];
  const tank2Data = data.tank2?.data || [];

  let csv = 'Period,Tank 1 (L),Tank 2 (L),Combined (L)\n';
  let totalTank1 = 0;
  let totalTank2 = 0;
  let totalCombined = 0;

  for (let i = 0; i < labels.length; i++) {
    const t1 = parseFloat(tank1Data[i] || 0);
    const t2 = parseFloat(tank2Data[i] || 0);
    const combined = t1 + t2;

    csv += `${labels[i]},${t1},${t2},${combined}\n`;

    totalTank1 += t1;
    totalTank2 += t2;
    totalCombined += combined;
  }

  csv += `Total,${totalTank1},${totalTank2},${totalCombined}\n`;

  // Trigger download
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = `${type}_water_usage_${from}_to_${to}.csv`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

// Initial load - Show All
window.onload = () => {
  document.getElementById('chart-type').value = 'all';
  loadSelectedChart();
};

function autoReloadOnStatisticsTab() {
    if (window.location.hash === "#statistics") {
        setTimeout(function() {
            window.location.reload();
        }, 2000); // Reload every 2 seconds
    }
}

// Run on page load
autoReloadOnStatisticsTab();

// Also run when the hash changes (user switches tabs)
window.addEventListener("hashchange", autoReloadOnStatisticsTab);
</script>

</body>
</html>
