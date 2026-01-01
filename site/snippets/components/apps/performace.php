

<script>
  const ctx = document.getElementById('container');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
      datasets: [
        {
        label: 'Requests per Day',
        data: 
        borderWidth: 1
        }
    ]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
</script>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
