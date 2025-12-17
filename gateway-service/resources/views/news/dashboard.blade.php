<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>News Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#f5f7fb;padding:24px}</style>
</head>
<body>
<div class="container">
    @include('components.header')

    @if(!empty($error))
        <div class="alert alert-danger">{{ $error }}</div>
    @endif

    @if(empty($news))
        <div class="card">
            <div class="card-body">No news available.</div>
        </div>
    @else
        <div class="row">
        @foreach($news as $item)
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $item['title'] ?? 'Untitled' }}</h5>

                        {{-- Description is shown only in the modal when the user clicks "Open" --}}

                        @if(!empty($item['image']) )
                            <?php $imageUrl = preg_match('/^https?:\/\//', $item['image']) ? $item['image'] : rtrim(config('services.news_service.url'), '/') . '/storage/' . ltrim($item['image'], '/'); ?>
                            <img src="{{ $imageUrl }}" class="img-fluid rounded mb-3" style="max-height:200px; object-fit:cover;">
                        @endif

                        <div class="mt-auto">
                            @php $openId = $item['id'] ?? $item['slug'] ?? null; @endphp
                            @if($openId)
                                <button type="button" class="btn btn-sm btn-primary btn-open" data-id="{{ $openId }}">Open</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="newsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newsModalTitle">News Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="newsModalBody">
                    <div class="text-center py-4">Loading...</div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

<script>
const NEWS_SERVICE_BASE = '{{ rtrim(config('services.news_service.url'), '/') }}';

// Small helper to escape HTML when inserting user content
function escapeHtml(s){
    if(!s && s !== 0) return '';
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

document.addEventListener('click', async function(e){
    if(!e.target.classList.contains('btn-open')) return;

    const id = e.target.dataset.id;
    const modalEl = document.getElementById('newsModal');
    const modal = new bootstrap.Modal(modalEl);
    const titleEl = modalEl.querySelector('#newsModalTitle');
    const bodyEl = modalEl.querySelector('#newsModalBody');

    titleEl.textContent = 'Loading...';
    bodyEl.innerHTML = '<div class="text-center py-4">Loading...</div>';
    modal.show();

    try {
        const res = await fetch('/news/' + id, { headers: { 'Accept': 'application/json' } });

        // If backend returned an error (404/401 etc), show its message if available
        if(!res.ok){
            let errText = res.statusText || 'Error';
            try{
                const errJson = await res.json();
                if(errJson && (errJson.error || errJson.message)) errText = errJson.error || errJson.message;
            }catch(e){/* ignore parse error */}
            console.error('News detail fetch failed', res.status, errText);
            bodyEl.innerHTML = `<div class="alert alert-danger">${escapeHtml(errText)}</div>`;
            return;
        }

        const json = await res.json();
        const item = json.data ?? json;

        titleEl.textContent = item.title ?? 'Detail';

        let html = '';
        if(item.description) html += `<p class="text-muted mb-3">${escapeHtml(item.description)}</p>`;
        if(item.image){
            const src = item.image.startsWith('http') ? item.image : `${NEWS_SERVICE_BASE}/storage/${item.image.replace(/^\//,'')}`;
            html += `<img src="${src}" class="img-fluid rounded mb-3" style="max-height:300px;object-fit:cover;">`;
        }
        if(item.body) html += `<div>${escapeHtml(item.body)}</div>`;

        bodyEl.innerHTML = html || '<p class="text-muted">No content</p>';
    } catch (err) {
        console.error('Failed to fetch news detail', err);
        bodyEl.innerHTML = `<div class="alert alert-danger">Failed to load news detail: ${escapeHtml(err.message || 'Unknown error')}</div>`;
    }
});
</script>

</body>
</html>
