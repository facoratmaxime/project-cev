@extends('layout')
@section('content')
    <div class=" flex items-top justify-center bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
        <div class="row">
            <div class="col-sm-12">
                <h1 class="display-4">Bonjour, </h1>
            </div>
        </div>
    </div>
    <div class=" flex items-top justify-center bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
        <div class="row">
            <div class="col-sm-12">
                <form action="{{ route('process.upload') }}" method="POST" enctype="multipart/form-data" id="form1">
                    @csrf
                    <div class="form-group">
                        <div class="custom-file">
                            <input accept=".csv" required type="file" class="custom-file-input" id="customFile"
                                name="customFile">
                            <label class="custom-file-label" for="customFile">Uploader votre fichier ici</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Valider</button>
                </form>

            </div>
        </div>

    </div>

    <div class="container">
            <a class="btn btn-primary" href="{{ route('process.upload_without_upload') }}">Utiliser le CSV par défaut</a>
            <a href="https://github.com/facoratmaxime/project-cev" target="_blank" class="btn btn-warning">Lien Github</a>
    </div>
    <script>
        $('#customFile').on('change',function(e){
            //get the file name
            var fileName = e.target.files[0].name;
            //replace the "Choose a file" label
            $(this).next('.custom-file-label').html(fileName);
        })
    </script>
@endsection
