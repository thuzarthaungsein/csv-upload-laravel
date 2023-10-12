import "./bootstrap";

const form = document.getElementById("form");
const inputFile = document.getElementById("upload-file");
var formData = new FormData();

form.addEventListener("submit", function (event) {
    event.preventDefault();
    formData.append("file", inputFile.files[0]);

    axios
        .post("/home", formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        })
        .then((response) => {
            var emptyFile = document.createElement("input");
            emptyFile.type = "file";
            inputFile.files = emptyFile.files;

            if (response.data.files.length > 0) {
                updateTableWithData(response.data.files);
            }
        });
});

const channel = Echo.channel("public.upload");

channel
    .subscribed(() => {
        console.log("subscribed");
    })
    .listen(".csv-upload", (event) => {
        console.log(event);

        const data = event.file;
        const progress = event.progress;
        var spanPercent = document.getElementById("percent" + data.id);
        var spanTime = document.getElementById("time" + data.id);

        spanTime.innerText = diffForHumans(new Date(data.created_at));

        if (progress !== null && spanPercent !== null) {
            if (progress.percentage < 1) {
                spanPercent.innerText = "Pending " + progress.percentage + " %";
            } else if (progress?.percentage == 100) {
                spanPercent.innerText = "Completed";
            } else {
                spanPercent.innerText =
                    "Processing " + progress.percentage + " %";
            }
        }
    });

function updateTableWithData(data) {
    // Assuming 'data' is an array of objects
    var tableBody = document.getElementById("upload-tbody");

    // Clear the existing table rows
    tableBody.innerHTML = "";

    // Loop through the data and create table rows
    data.forEach(function (item) {
        var row = tableBody.insertRow();
        var cell1 = row.insertCell(0);
        var cell2 = row.insertCell(1);
        var cell3 = row.insertCell(2);

        var spanTime = document.createElement("span");
        spanTime.setAttribute("id", "time" + item.id);
        spanTime.innerText = diffForHumans(new Date(item.created_at));

        var spanPercent = document.createElement("span");
        spanPercent.setAttribute("id", "percent" + item.id);

        if (item.progress.percentage < 1) {
            spanPercent.innerText =
                "Pending " + item.progress.percentage + " %";
        } else if (item.progress.percentage == 100) {
            spanPercent.innerText = "Completed";
        } else {
            spanPercent.innerText =
                "Processing " + item.progress.percentage + " %";
        }

        // Populate the cells with data
        cell1.append(spanTime);
        cell2.innerHTML = item.original_name;
        cell3.append(spanPercent);
    });
}

function diffForHumans(unixTime, ms) {
    // Adjust for milliseconds
    ms = ms || false;
    unixTime = ms ? unixTime * 1000 : unixTime;

    var d = new Date();
    var diff = Math.abs(d.getTime() - unixTime);
    var intervals = {
        y: diff / (365 * 24 * 60 * 60 * 1 * 1000),
        m: diff / (30.5 * 24 * 60 * 60 * 1 * 1000),
        d: diff / (24 * 60 * 60 * 1 * 1000),
        h: diff / (60 * 60 * 1 * 1000),
        i: diff / (60 * 1 * 1000),
        s: diff / (1 * 1000),
    };

    Object.keys(intervals).map(function (value, index) {
        return (intervals[value] = Math.floor(intervals[value]));
    });

    var unit;
    var count;

    switch (true) {
        case intervals.y > 0:
            count = intervals.y;
            unit = "year";
            break;
        case intervals.m > 0:
            count = intervals.m;
            unit = "month";
            break;
        case intervals.d > 0:
            count = intervals.d;
            unit = "day";
            break;
        case intervals.h > 0:
            count = intervals.h;
            unit = "hour";
            break;
        case intervals.i > 0:
            count = intervals.i;
            unit = "minute";
            break;
        default:
            count = intervals.s;
            unit = "second";
            break;
    }

    if (count > 1) {
        unit = unit + "s";
    }

    if (count === 0) {
        return "now";
    }

    return count + " " + unit + (unixTime > d.getTime() ? " from now" : " ago");
}
