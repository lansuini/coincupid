@extends('/GM/Layout')


@section('script')

<script type="text/javascript">
Swal.fire({
  title: 'Tips',
  html: ' <h3> <span class="badge {{ $success ? 'badge-success' : 'badge-danger'}}">Message: {{ $message }}</span> <br> after <strong></strong>s auto redirect </h3>',
  timer: {{ $waitTime * 1000 }},
  width: 900,
  didOpen: () => {
    const content = Swal.getHtmlContainer()
    const $ = content.querySelector.bind(content)

    // const stop = $('#stop')
    // const resume = $('#resume')
    // const toggle = $('#toggle')
    // const increase = $('#increase')

    Swal.showLoading()

    function toggleButtons () {
      stop.disabled = !Swal.isTimerRunning()
      resume.disabled = Swal.isTimerRunning()
    }

    // stop.addEventListener('click', () => {
    //   Swal.stopTimer()
    //   toggleButtons()
    // })

    // resume.addEventListener('click', () => {
    //   Swal.resumeTimer()
    //   toggleButtons()
    // })

    // toggle.addEventListener('click', () => {
    //   Swal.toggleTimer()
    //   toggleButtons()
    // })

    // increase.addEventListener('click', () => {
    //   Swal.increaseTimer(5000)
    // })

    timerInterval = setInterval(() => {
      Swal.getHtmlContainer().querySelector('strong')
        .textContent = (Swal.getTimerLeft() / 1000)
          .toFixed(0)
    }, 100)
  },
  willClose: () => {
    clearInterval(timerInterval)
    window.location = '{{ $url }}'
  }
})
</script>

@endsection