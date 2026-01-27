<style>
    .desc-checkbox {
        margin-right: 10px; /* Space between checkbox and text */
        cursor: pointer;
    }
    .desc-text.checked {
        text-decoration: line-through;
        color: green;
    }
</style>

<div class="modal-body">
    <div class="row">
        <div class="form-group">
            @php
                $descriptions = json_decode($desc->description, true);
            @endphp
            @if(!empty($descriptions))
                <ol class="p-3 text-wrap text-break list-unstyled">
                    @foreach($descriptions as $index => $item)
                        <li class="d-flex align-items-center">
                            <input type="checkbox" class="desc-checkbox me-2"
                                data-id="{{ $desc->id }}"
                                data-index="{{ $index }}"
                                onchange="updateDescriptionStatus(this)"
                                {{ isset($item['completed']) && $item['completed'] ? 'checked' : '' }}>
                            <span class="desc-text {{ isset($item['completed']) && $item['completed'] ? 'checked' : '' }}">
                                {{ $item['text'] }}
                            </span>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="p-3 text-wrap text-break">{{ __('No descriptions available.') }}</p>
            @endif
        </div>
    </div>
</div>

<script>
    function updateDescriptionStatus(checkbox) {
        let todoId = checkbox.dataset.id;
        let index = checkbox.dataset.index;
        let isChecked = checkbox.checked;

        console.log("Updating description:", { todoId, index, isChecked });

     fetch("{{ url('/todo/to-do/update-description') }}/" + todoId, {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").getAttribute("content"),
    },
    body: JSON.stringify({ index, completed: isChecked }),
})

        .then(response => response.json())
        .then(data => {
            if (data.success) {
                checkbox.nextElementSibling.classList.toggle('checked', isChecked);
            } else {
                console.error("Failed to update:", data.message);
            }
        })
        .catch(error => console.error("Error updating:", error));
    }
</script>
