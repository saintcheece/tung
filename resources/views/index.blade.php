<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel</title> 

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

        <!-- Styles -->
        <style>
        </style>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    </head>
    <body class="antialiased bg-dark text-white d-flex flex-column justify-content-center align-items-center min-vh-100">
        <h1>tung</h1>
        <p>translate texts from images</p>
        <form action="{{ route('image-to-text.store') }}" method="post" enctype="multipart/form-data" style="height: 75vh;" class="w-75 d-flex flex-column justify-content-center">
            @csrf
            <div class="d-flex">
                <div class="w-50 h-50">
                    <div class="input-group p-3">
                        <input type="file" name="toText" id="toText" class="form-control bg-dark text-white" accept="image/jpeg">
                        <button type="submit" class="btn btn-primary" id="send">Upload</button>
                    </div>
                    <div id="preview" class="w-50 mx-auto d-flex align-items-center justify-content-center"></div>

                    <script>
                        document.getElementById('toText').addEventListener('change', function(event) {
                            const file = event.target.files[0];
                            const preview = document.getElementById('preview');

                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    preview.innerHTML = `<img src="${e.target.result}" alt="Image Preview" class="img-fluid" />`;
                                };
                                reader.readAsDataURL(file);
                            } else {
                                preview.innerHTML = '';
                            }
                        });
                    </script>
                </div>
                <div class="d-flex flex-column w-50">
                    <div class="input-group p-3">
                        <label class="input-group-text" for="toLang">Translate to</label>
                        <select class="form-select bg-dark text-white" id="toLang" name="toLang">
                            <option value="en">English</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                            <option value="zh">Chinese</option>
                            <option value="ja">Japanese</option>
                            <option value="hi">Hindi</option>
                            <option value="ar">Arabic</option>
                            <option value="pt">Portuguese</option>
                            <option value="ru">Russian</option>
                        </select>
                    </div>
                    <div class="overflow-auto bg-secondary border-secondary-subtle p-3 rounded" style="height: 50vh;">
                        <p class="text-break" id="response">
                            Choose a Photo then Upload to Translate
                        </p>
                    </div>  
                </div>  
            </div>
            <small class="px-3 text-secondary">*tung only supports .jpg and <a href="https://en.wikipedia.org/wiki/List_of_Latin-script_letters">latin-script characters</a></small>
            <div>
            </div>
        </form>
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    </body>
    <script>
        initialResponse = '';
        document.querySelector("form").addEventListener("submit", function(e) {
            e.preventDefault();
            var submitButton = this.querySelector('button[type=submit]');
            submitButton.disabled = true;
            submitButton.textContent = 'Translating...';
            document.getElementById('response').innerHTML = 'Awaiting response...';
            var formData = new FormData(this);
            axios.post(this.action, formData)
            .then(function(response) {
                // initialResponse = response.data.candidates[0].content.parts[0].text;
                // Object.entries(response.data).forEach(([key, value]) => {
                //     document.getElementById('response').innerHTML += `from ${key}: ${value}<br>`;
                // });
                document.getElementById('response').innerHTML = response.data;
                // document.getElementById('explain').disabled = false;
            }.bind(this))
            .catch(function(error) {
                document.getElementById('response').innerHTML = error + "<br> <p>PLEASE TRY AGAIN OR WITH A MORE READABLE TEXT</p>";
                this.querySelector('button[type=submit]').disabled = false;
            }.bind(this));
            submitButton.disabled = false;
            submitButton.textContent = 'Translate';
        });
        document.querySelector('#toText').addEventListener("change", function() {
            document.querySelector('button[type=submit]').disabled = this.files.length === 0;
        });
        document.querySelector('#toLang').addEventListener("change", function() {
            if (document.getElementById('toText').files.length === 0) {
                return;
            }
            var submitButton = document.getElementById('send');
            submitButton.disabled = true;
            submitButton.textContent = 'Translating...';
            document.getElementById('response').innerHTML = 'Awaiting response...';
            axios.post("{{ route('image-to-text.retranslate') }}", {
                readText: document.getElementById('response').textContent,
                language: this.value
            })
            .then(function(response) {
                document.getElementById('response').innerHTML = response.data;
            })
            .catch(function(error) {
                document.getElementById('response').innerHTML = error + "<br> <p>PLEASE TRY AGAIN OR WITH A MORE READABLE TEXT</p>";
                this.querySelector('button[type=submit]').disabled = false;
            });
            submitButton.disabled = false;
            submitButton.textContent = 'Translate';
        });
    </script>
</html>