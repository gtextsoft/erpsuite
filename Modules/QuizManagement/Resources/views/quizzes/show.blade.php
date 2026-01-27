<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <table class="table modal-table">
                <tbody>
                    @foreach ($quizQuestions as $key => $quizQuestion)
                        <tr>
                            <th>{{ ++$key }}</th>
                            <td>
                                {{ optional($quizQuestion)->question ?? '-' }}
                                <b>({{ optional($quizQuestion)->marks ?? '-' }} {{ __('Marks') }})</b>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
