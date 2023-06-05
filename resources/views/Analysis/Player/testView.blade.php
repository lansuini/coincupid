<!doctype html>
        <html>
        <head>
            <title>ECharts Sample</title>
            <!-- <script src="https://cdn.jsdelivr.net/npm/echarts@5.3.2/dist/echarts.min.js"></script> -->

            <script src="/adminlte/custom/echarts.min.js"></script>
        </head>
        <body>
            <div id="echart-bar" style="width: 500px; height: 350px;"></div>
            <script>

            //You need to quote your labels, otherwise will get an unexpected token in JSON
            var series='[{"name":"Complete Project", "type":"bar", "data":[2.0, 4.9, 7.0, 23.2, 25.6, 76.7, 135.6, 162.2, 32.6, 20.0, 6.4, 3.3]},{"name":"New Project", "type":"bar", "data":[2.6, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6, 182.2, 48.7, 18.8, 6.0, 2.3]}]';

            //Call the function 
            getBarGraph(series);

            function getBarGraph(data){
                var dom = document.getElementById('echart-bar');
                var myChart = echarts.init(dom);
                var option = {
                    title: { text: 'ECharts Sample' },
                    tooltip : {
                        trigger: 'axis'
                    },

                     xAxis : [
                        {
                            type : 'category',
                            data : ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
                        }
                    ],
                    yAxis : [{ type : 'value' }],

                    //Use the JavaScript function JSON.parse() to convert text into a JavaScript object:
                    series: JSON.parse(data),
                };
                myChart.setOption(option);

            }
            </script>
        </body>
        </html>