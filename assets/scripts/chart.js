import Chart from 'chart.js/auto';
import Routing from 'fos-router';
import daterangepicker from 'daterangepicker';

const dates = {
    startYear: 'first day of jan',
    endYear: 'first day of jan next year -1 sec',
    startMonth: 'first day of',
    endMonth: 'first day of next month -1 sec',
    startTwoWeeks: '-2 week monday midnight',
    endTwoWeeks: 'sunday 23:59:59',
}

let charts = {};
const canvases = document.querySelectorAll('canvas');

canvases.forEach((el) => {
    charts[el.id] = null;
})

const dataDefault = await fetchOrdersData(
    dates.startYear,
    dates.endYear,
    'month'
);
const canvasMain = document.querySelector('#canvas_sales');

charts[canvasMain.id] =  await chartDraw(canvasMain, dataDefault, 'Sales by month');
$(document).ready(() => {
    $(function() {
        $('input[name="daterange"]').daterangepicker({
            opens: 'left'
        },function(start, end, label) {
            let canvasMain = document.querySelector('#canvas_sales');

            fetchOrdersData(
                start,
                end,
                'day'
            ).then(async (dataDatePicker) => {
                charts[canvasMain.id].destroy();
                charts[canvasMain.id] = await chartDraw(canvasMain, dataDatePicker, 'Sales by day');
            });
        });
    });
});
async function addNewChart(interval, startDate, endDate) {
    let perInterval;
    switch (interval) {
        case 'month':
            perInterval = 'week';
            break;
        case 'week':
            perInterval = 'day';
            break;
        default:
            perInterval = 'day';
            break;
    }

    let data = await fetchOrdersData(
        startDate,
        endDate,
        perInterval
    );

    for (let i = 0; i < canvases.length; i++) {
        if (charts[canvases[i].id] == null) {
            charts[canvases[i].id] = await chartDraw(canvases[i], data, 'Sales by ' + perInterval);
            return true;
        }
    }
    return false;
}
async function chartDraw(canvas, data, datasetLabel) {
    return new Chart(
        canvas,
        {
            type: 'bar',
            data: {
                labels: data.items.map(row => row.labelInterval),
                datasets: [
                    {
                        label: datasetLabel,
                        data: data.items.map(row => row.sumTotalCost),
                    },
                ]
            },
            options: {
                onClick: async (event, element, chart) => {

                    for (let i = 0; i < canvases.length; i++) {
                        if (charts[canvases[i].id] === chart) {
                            for (let j = i+1; j < canvases.length; j++) {
                                if (charts[canvases[j].id] != null) {
                                    charts[canvases[j].id].destroy();
                                    charts[canvases[j].id] = null;
                                }
                            }
                            break;
                        }
                    }

                    await addNewChart(data.interval, data.items[element[0].index].startDate.date, data.items[element[0].index].endDate.date);
                }
            }
        }
    );
}

async function fetchOrdersData(startDate, endDate, dataPerInterval) {
    const data = new FormData();
    data.append('startDate', startDate);
    data.append('endDate', endDate);
    data.append('dataPerInterval', dataPerInterval);

    let res = await fetch(Routing.generate('api_orders_between_dates'), {
        method: "POST",
        body: data,
    });

    return await res.json();
}