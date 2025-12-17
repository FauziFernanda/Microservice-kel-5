@if(session('access_token'))
    <!-- Logout button opens confirmation modal -->
    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</button>

    <!-- Hidden form to perform logout (submits CSRF token) -->
    <form method="POST" action="/logout" class="d-none" id="logoutForm">
        @csrf
    </form>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Are you sure you want to logout?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmLogoutBtn">Yes, logout</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('click', function(e){
            if(e.target && e.target.id === 'confirmLogoutBtn'){
                document.getElementById('logoutForm').submit();
            }
        });
    </script>
@else
    <div class="d-flex gap-2">
        <a href="/login" class="btn btn-sm btn-outline-primary">Login</a>
        <a href="/register" class="btn btn-sm btn-primary">Register</a>
    </div>
@endif
