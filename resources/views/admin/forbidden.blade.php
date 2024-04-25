<!DOCTYPE html>
<html lang="en">


    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Jeeb Admin Panel</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>


    <body>
        <div class="d-flex align-items-center justify-content-center vh-100">
            <div class="text-center">
                <h1 class="display-1 fw-bold">403</h1>
                <p class="fs-3"> <span class="text-danger">Error</span></p>
                <p class="lead">
                    Sorry! You dont have permission to view this page.
                  </p>
                <a href="{{ redirect()->getUrlGenerator()->previous() }}" class="btn btn-primary">Go Back</a>
            </div>
        </div>
    </body>


</html>