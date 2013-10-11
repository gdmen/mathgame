$(function () {
  var operators_data = $.parseJSON($('#operators_data').text());
  $('#operators').highcharts({
    chart: {
      plotBackgroundColor: null,
      plotBorderWidth: null,
      plotShadow: false
    },
    title: {
      text: 'Operator distribution'
    },
    tooltip: {
      shared: true,
      formatter: function() {
        return this.point.name +': <b>' + this.point.percentage.toFixed(1) + '%</b>';
      }
    },
    plotOptions: {
      pie: {
        allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
          enabled: false
        },
        showInLegend: true
      }
    },
    series: [{
      type: 'pie',
      name: 'percentage',
      data: operators_data
    }]
  });
  var efficiency_data = $.parseJSON($('#efficiency_data').text());
  $('#efficiency').highcharts({
    chart: {
      type: 'bubble',
      spacingRight: 20,
      zoomType: 'x',
    },
    title: {
      text: 'Efficiency'
    },
    subtitle: {
      text: 'point size corresponds to # of problems in the session<br />(click and drag to zoom)'
    },
    xAxis: {
      type: 'datetime',
    },
    yAxis: {
      title: {
        text: 'Percent time doing math'
      },
      max: 100,
      min: 0
    },
    tooltip: {
      shared: true,
      formatter: function() {
        return Highcharts.dateFormat('%b %e', this.x) +': '+ this.point.z + ' problems at ' + this.y +'%';
      }
    },
    legend: {
      enabled: false
    },
    plotOptions: {
      bubble: {
        minSize: 5,
        maxSize: 30,
      },
    },
    series: [{
      type: 'bubble',
      pointInterval: 24 * 3600 * 1000,
      pointStart: efficiency_data[0][0],
      name: 'Percent work',
      // Define the data points. All series have a dummy year
      // of 1970/71 in order to be compared on the same x axis. Note
      // that in JavaScript, months start at 0 for January, 1 for February etc.
      data: efficiency_data
    }]
  });
  var num_problems_data = $.parseJSON($('#num_problems_data').text());
  $('#num_problems').highcharts({
    chart: {
      type: 'line',
      spacingRight: 20,
      zoomType: 'x',
    },
    title: {
      text: 'Cumulative Problems Solved'
    },
    subtitle: {
      text: '(click and drag to zoom)'
    },
    xAxis: {
      type: 'datetime',
    },
    yAxis: {
      title: {
        text: '# of Problems'
      },
      min: 0
    },
    tooltip: {
      shared: true,
      formatter: function() {
        return Highcharts.dateFormat('%e. %b', this.x) +': '+ this.y +' problems';
      }
    },
    legend: {
      enabled: false
    },
    plotOptions: {
      line: {
        marker: {
          enabled: false
        }
      },
    },
    series: [{
      type: 'line',
      pointInterval: 24 * 3600 * 1000,
      pointStart: num_problems_data[0][0],
      name: 'series name',
      // Define the data points. All series have a dummy year
      // of 1970/71 in order to be compared on the same x axis. Note
      // that in JavaScript, months start at 0 for January, 1 for February etc.
      data: num_problems_data
    }]
  });
  /*
  var improvement_data = $.parseJSON($('#improvement_data').text());
  $('#improvement').highcharts({
    chart: {
      type: 'line',
      spacingRight: 20
    },
    title: {
      text: 'Progress'
    },
    subtitle: {
      text: 'subtitle'
    },
    xAxis: {
      type: 'datetime',
    },
    yAxis: {
      title: {
        text: 'Cumulative improvement by problem (seconds)'
      },
    },
    tooltip: {
      shared: true
    },
    legend: {
      enabled: false
    },
    plotOptions: {
      line: {
        marker: {
          enabled: false
        }
      },
      area: {
        fillColor: {
          linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1},
          stops: [
              [0, Highcharts.getOptions().colors[0]],
              [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
          ]
        },
        lineWidth: 1,
        marker: {
          enabled: false
        },
        shadow: false,
        states: {
          hover: {
            lineWidth: 1
          }
        },
        threshold: null
      }
    },
    series: [{
      type: 'line',
      pointInterval: 24 * 3600 * 1000,
      pointStart: improvement_data[0][0],
      name: 'Improvement',
      // Define the data points. All series have a dummy year
      // of 1970/71 in order to be compared on the same x axis. Note
      // that in JavaScript, months start at 0 for January, 1 for February etc.
      data: improvement_data
    }]
  });*/
});
    
