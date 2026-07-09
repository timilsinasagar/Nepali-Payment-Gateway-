{{-- resources/views/redirect-form.blade.php --}}
{{--
    eSewa v2 requires the browser itself to POST a signed form directly
    to their endpoint -- there is no redirect URL to hand the user like
    Khalti provides. This view builds that form and submits it
    automatically. The "Redirecting..." message and disabled JS
    fallback below are there in case the browser blocks the auto-submit.
--}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Redirecting to eSewa...</title>
</head>

<body>

    <p>Redirecting to eSewa, please wait...</p>

    <form id="esewa-redirect-form" method="POST" action="{{ $formAction }}">
        @foreach ($formFields as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <noscript>
            <button type="submit">Continue to eSewa</button>
        </noscript>
    </form>

    <script>
        document.getElementById('esewa-redirect-form').submit();
    </script>

</body>

</html>