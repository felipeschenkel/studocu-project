<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simple Form</title>

    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <link href="./resources/css/app.css" rel="stylesheet">

    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
            background: url('https://storage.googleapis.com/chile-travel-static-content/2019/07/camping-portada-2.jpg')
        }

        label {
            color: black;
            font-weight: 700;
        }

        form {
            background: white;
            opacity: 0.9;
            height: 900px;
            width: auto;
            max-width: 800px;
        }

        .form {
            margin-left: 10%;
        }

        input, select {
            opacity: 0.8;
        }

        .title {
            font-weight: 700;
        }

        .button {
            width: 300px;
            background: lightgreen;
            color: black;
        }
    </style>
</head>
<body>
    <div class="flex-center position-ref full-height">
        <form>
            <div class="form">
                <div class="title">SAVE YOUR DATA AND GO CAMPING!</div> <br />
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" /> <br />

                <label for="country">Country</label>
                <select id="country">
                    @foreach($countries as $country)
                        <option value="{{$country->iso}}">{{$country->name}}</option>
                    @endforeach
                </select> <br />

                <label for="birth_date">Birth Date</label>
                <input type="date" id="birth_date" /> <br /> <br />

                <input class="button" type="button" onclick="save()" value="SEND">
            </div>
        </form>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    function save() {
        if (document.getElementById('full_name').value === '') {
            alert('Name cannot be empty')
            document.getElementById("full_name").focus();
            return
        }
        if (document.getElementById('birth_date').value === '') {
            alert('Birth date cannot be empty')
            document.getElementById("birth_date").focus();
            return
        }
        axios.post('/saveForm', {
            name: document.getElementById('full_name').value,
            country_iso: document.getElementById('country').value,
            birth_date: document.getElementById('birth_date').value
        })
            .then(function (response) {
                if (response) {
                    alert('Register saved!')
                } else {
                    alert('Something went wrong')
                }
            })
            .catch(function () {
                alert('Error saving your data')
            });
    }
</script>
</html>
