@extends('front.layouts.app')

@section('content')
<section class="container">
    <div class="col-md-12 text-center py-5">
        <div class="alert alert-success">
            @if (Session::has('success'))
                    <div class="alert alert-success">
                        {{ Session::get('success') }}
                    </div>
            @endif
        </div>
        <h1>Thank you!</h1>
        <p> Your Order ID is: {{ $id }}</p>
    </div>


</section>


@endsection


@section('customjs')
<script>


</script>
@endsection