<!DOCTYPE html>
<html>

<head>
  <title>Dynamic Graph with ApexCharts</title>
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-family: Arial, sans-serif;
    }

    #chart {
      width: 50vw;
      height: 50vh;
    }

    #filterButtons {
      display: flex;
      justify-content: center;
      margin-top: 20px;
    }

    button {
      padding: 8px 16px;
      margin: 0 5px;
      font-size: 14px;
      border: none;
      background-color: #007bff;
      color: #fff;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }
  </style>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.28.3/dist/apexcharts.min.css">
  <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.28.3/dist/apexcharts.min.js"></script>
</head>

<body>
  <div id="chart"></div>
  <!-- Add a select dropdown for year selection -->
  <select id="yearSelect" class="form-control">
    <option value="">Select year</option>
    <option value="2022">2022</option>
    <option value="2023">2023</option>
    <!-- Add more options as needed -->
  </select>

  <!-- Add a container for the chart -->
  <div id="chartContainer"></div>
  <div id="filterButtons">
    <button onclick="updateChart('yearly')">Yearly</button>
    <button onclick="updateMonthlyChart()">Monthly</button>
    <button onclick="updateChart('weekly')">Weekly</button>
  </div>

  <script>
    // Sample data
    var data = [{
        date: '2015-06-28',
        value: 45
      },
      {
        date: '2018-06-28',
        value: 45
      },
      {
        date: '2020-06-28',
        value: 45
      },
      {
        date: '2021-06-28',
        value: 45
      },
      {
        date: '2022-06-28',
        value: 45
      },
      {
        date: '2023-06-28',
        value: 45
      },
      {
        date: '2023-07-01',
        value: 50
      },
      {
        date: '2023-07-05',
        value: 25
      },
      {
        date: '2023-07-07',
        value: 30
      },
      {
        date: '2023-07-10',
        value: 20
      },
      {
        date: '2023-07-11',
        value: 20
      },
      {
        date: '2023-07-12',
        value: 20
      },
      {
        date: '2023-07-15',
        value: 40
      },
      // Add more data entries
      // ...
    ];

    // Initial chart options
    var options = {
      chart: {
        type: 'bar',
        toolbar: {
          show: false
        }
      },
      series: [],
      xaxis: {
        categories: [],
        labels: {
          style: {
            fontSize: '12px'
          }
        }
      },
      yaxis: {
        labels: {
          style: {
            fontSize: '12px'
          }
        }
      },
      colors: ['#007bff'],
      dataLabels: {
        enabled: false
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '50%'
        }
      },
      grid: {
        borderColor: '#eee',
        row: {
          colors: ['#f3f3f3', 'transparent'],
          opacity: 0.5
        }
      },
      tooltip: {
        theme: 'light',
        x: {
          format: 'dd/MM/yyyy'
        }
      }
    };

    // Create the chart instance
    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();

    // Function to generate group-wise summed data based on filter type (yearly, monthly, weekly)
    function filterData(filterType, filterYear = null) {
      var groupedData = {};

      switch (filterType) {
        case 'yearly':
          // Get the range of years from the data
          var minYear = Math.min.apply(Math, data.map(function(entry) {
            return new Date(entry.date).getFullYear();
          }));
          var maxYear = Math.max.apply(Math, data.map(function(entry) {
            return new Date(entry.date).getFullYear();
          }));

          // Initialize groupedData with all years and zero values
          for (var year = minYear; year <= maxYear; year++) {
            groupedData[year] = 0;
          }

          // Sum the values for each year
          data.forEach(function(entry) {
            var entryYear = new Date(entry.date).getFullYear();
            groupedData[entryYear] += entry.value;
          });
          break;
        case 'monthly':
          var filteredData = data.filter(function(entry) {
            var entryYear = new Date(entry.date).getFullYear().toString();
            return !filterYear || entryYear === filterYear;
          });

          // Get an array of all months in a year
          var months = Array.from({
            length: 12
          }, function(_, i) {
            return new Date(0, i).toLocaleString('default', {
              month: 'long'
            });
          });

          // Initialize groupedData with all months and zero values
          months.forEach(function(month) {
            groupedData[month] = 0;
          });

          // Sum the values for each month in the filtered data
          filteredData.forEach(function(entry) {
            var entryMonth = new Date(entry.date).toLocaleString('default', {
              month: 'long'
            });
            groupedData[entryMonth] += entry.value;
          });
          break;
        case 'weekly':
          // Get the current week's start and end dates
          var currentDate = new Date();
          var currentWeek = getWeekNumber(currentDate);
          var weekStart = new Date(currentDate.getFullYear(), 0, (currentWeek - 1) * 7);
          var weekEnd = new Date(currentDate.getFullYear(), 0, (currentWeek - 1) * 7 + 6);

          // Initialize groupedData with all dates within the week and zero values
          var dateCursor = new Date(weekStart);
          while (dateCursor <= weekEnd) {
            var formattedDate = dateCursor.toLocaleDateString('default', {
              month: 'short',
              day: 'numeric'
            });
            groupedData[formattedDate] = 0;
            dateCursor.setDate(dateCursor.getDate() + 1);
          }

          // Sum the values for each date within the week
          data.forEach(function(entry) {
            var entryDate = new Date(entry.date);
            if (entryDate >= weekStart && entryDate <= weekEnd) {
              var formattedDate = entryDate.toLocaleDateString('default', {
                month: 'short',
                day: 'numeric'
              });
              groupedData[formattedDate] += entry.value;
            }
          });
          break;

      }

      return groupedData;
    }


    // Function to get week number of a given date
    function getWeekNumber(date) {
      var onejan = new Date(date.getFullYear(), 0, 1);
      var millisecsInDay = 86400000;
      return Math.ceil((((date - onejan) / millisecsInDay) + onejan.getDay() + 1) / 7);
    }


    // Function to update the chart based on filter type and optional year filter
    // function updateChart(filterType, filterYear = null) {
    //   var groupedData = filterData(filterType, filterYear);

    //   var chartData = Object.keys(groupedData).map(function(key) {
    //     return {
    //       x: key,
    //       y: groupedData[key]
    //     };
    //   });

    //   chart.updateSeries([{
    //     data: chartData
    //   }]);
    //   chart.updateOptions({
    //     xaxis: {
    //       categories: Object.keys(groupedData)
    //     }
    //   });
    // }

    function updateChart(filterType, filterYear = null) {
  var groupedData = filterData(filterType, filterYear);

  var chartData = Object.keys(groupedData).map(function (key) {
    return { x: key, y: groupedData[key], label: groupedData[key].toString() };
  });

  chart.updateSeries([{ data: chartData }]);
  chart.updateOptions({
    xaxis: { categories: Object.keys(groupedData) },
    dataLabels: { enabled: true, formatter: function(val) { return val; } },
    tooltip: {
      enabled: true,
      y: {
        formatter: function(val) {
          return "Value: " + val;
        }
      },
      custom: function({ series, seriesIndex, dataPointIndex, w }) {
        return w.config.xaxis.categories[dataPointIndex] + ": " + w.globals.series[seriesIndex][dataPointIndex];
      }
    }
  });
}




    // Initial chart with yearly data
    updateChart('yearly');
  </script>
  <script>
    // Function to update the chart based on the selected year
    function updateMonthlyChart() {
      var yearSelect = document.getElementById("yearSelect");
      var selectedYear = yearSelect.value;

      updateChart("monthly", selectedYear);
    }
  </script>
</body>

</html>