<section class="card">
    <h1>Create account</h1>
    <form method="POST" action="/register">
        <div class="form-group">
            <label for="full_name">Full name</label>
            <input id="full_name" name="full_name" type="text" placeholder="Jane Doe">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" placeholder="you@example.com">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Password">
        </div>
        <button class="btn" type="submit">Register</button>
    </form>
</section>
