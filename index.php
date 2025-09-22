<?php
    // require __DIR__ . '/../water/authentication.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Water Tank Monitor</title>
  <link rel="stylesheet" href="style.css">
  <style>


  </style>
</head>
<body>
  <div id="monitoring" class="tab-content active">
    <div class="dashboard-grid">
      <div class="card-container">
        <h2 style="text-align: center; margin-bottom: 20px; color: #333;">Virtual Water Tank</h2>
        <div class="tank-container">
          <!-- TANK 1 -->
          <div class="tank">
            <div class="tank-header">
              <h2>Tank 1</h2>
            </div>
            <div class="info">
              <span id="tank1-percent">0%</span> • <span id="tank1-volume">0.00</span>L
            </div>
            <div class="tank-body">
              <div id="tank1-bar" class="water" style="height: 0%"></div>
            </div>
          </div>
        
          <!-- TANK 2 -->
          <div class="tank">
            <div class="tank-header">
              <h2>Tank 2</h2>
            </div>
            <div class="info">
              <span id="tank2-percent">0%</span> • <span id="tank2-volume">0.00</span>L
            </div>
            <div class="tank-body">
              <div id="tank2-bar" class="water" style="height: 0%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const TANK_TOTAL_VOLUME = 3.0;
    let prevVol1 = null;
    let prevVol2 = null;

    function processReading(raw) {
      if (raw <= 14) return 0;
      if (raw >= 79) return 100;
      return raw;
    }

    function logLitersChange(tank, prevLiters, currentLiters) {
      const change = Math.abs(prevLiters - currentLiters); // always positive
      if (change !== 0) {
        fetch('log_liters_change.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            tank,
            previous: prevLiters,
            current: currentLiters,
            change,
            timestamp: new Date().toISOString()
          })
        }).catch(err => console.error('Error logging:', err));
      }
    }

    async function fetchData() {
      try {
        const response = await fetch('data.json?' + new Date().getTime());
        if (!response.ok) throw new Error('Fetch failed');
        const data = await response.json();

        const percent1 = processReading(data.tank1);
        const percent2 = processReading(data.tank2);

        const vol1 = (percent1 / 100) * TANK_TOTAL_VOLUME;
        const vol2 = (percent2 / 100) * TANK_TOTAL_VOLUME;

        if (prevVol1 !== null) logLitersChange('tank1', prevVol1, vol1);
        if (prevVol2 !== null) logLitersChange('tank2', prevVol2, vol2);

        prevVol1 = vol1;
        prevVol2 = vol2;

        document.getElementById('tank1-percent').textContent = percent1 + '%';
        document.getElementById('tank1-volume').textContent = vol1.toFixed(2);
        document.getElementById('tank1-bar').style.height = percent1 + '%';

        document.getElementById('tank2-percent').textContent = percent2 + '%';
        document.getElementById('tank2-volume').textContent = vol2.toFixed(2);
        document.getElementById('tank2-bar').style.height = percent2 + '%';


        fetch('check_tank_levels.php')
          .then(res => res.json())
          .then(data => console.log('Check tank response:', data))
          .catch(err => console.error('Check email error:', err));

      } catch (err) {
        console.error('Fetch error:', err);
      }
    }

    // setInterval(() => {
    //   fetch('check_tank_levels.php').catch(err => console.error('Check email error:', err));
    // }, 2000);
    // fetchData();
    // Update every 2 seconds
  setInterval(fetchData, 2000);
  fetchData(); // initial call
  </script>
</body>
</html>
