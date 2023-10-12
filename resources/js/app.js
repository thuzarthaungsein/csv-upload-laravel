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

        var spanText = document.createElement("span");
        spanText.setAttribute("id", "percent" + item.id);

        if (item.progress.percentage < 1) {
            spanText.innerText = "Pending " + item.progress.percentage + " %";
        } else if (item.progress.percentage == 100) {
            spanText.innerText = "Completed";
        } else {
            spanText.innerText =
                "Processing " + item.progress.percentage + " %";
        }

        // Populate the cells with data
        cell1.innerHTML = item.created_at;
        cell2.innerHTML = item.original_name;
        cell3.append(spanText);
    });
}
