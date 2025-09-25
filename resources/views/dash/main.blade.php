<x-dash.layout>
    <h1>ว่าไง!! {{ Auth::user()->name }} {{ Auth::user()->surname }}</h1>
    <form method="POST" action=" {{ route('auth.web.logout') }} ">
        @csrf
        <button type="submit" class="btn btn-danger">Logout</button>
    </form>
</x-dash.layout>
