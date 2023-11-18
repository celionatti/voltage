<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title')</title>
  </head>
  <body>
    <header>
      <h1>Nav Section</h1>
    </header>

    <main>
      @yield('content')
      <p>This is a sample view content.</p>
    </main>

    <footer>
      <h1>Footer Section</h1>
    </footer>
  </body>
</html>
