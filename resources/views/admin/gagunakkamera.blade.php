<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>E-Absen Asrama Polbangtan-mlg</title>
    <link rel="icon" href="{{ asset('img/logo-asrama.png') }}">
    {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-200">
    <div class="container mx-auto">
        <div class="bg-white shadow-lg w-[500px] rounded p-5 mx-auto mt-12">
            <h4 class="inline">Pilih kamera</h4>
            <select id="pilihKamera"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.">
                <option selected>Select camera devices</option>
            </select>
            <div class="flex flex-row">
                <div class="previewParent">
                    <div class="text-center">
                        <h4 class="hidden" id="searching">Mencari...</h4>
                    </div>
                    <video id="previewKamera"></video>
                </div>
            </div>
            <div id="hasilScan"></div>
        </div>

        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr>
                    <th class="border-b px-4 py-2">id</th>
                    <th class="border-b px-4 py-2">user_id</th>
                    <th class="border-b px-4 py-2">attendance_id</th>
                    <th class="border-b px-4 py-2">presence_date</th>
                    <th class="border-b px-4 py-2">presence_morning</th>
                    <th class="border-b px-4 py-2">presence_night</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td class="border-b px-4 py-2">{{ $item->id }}</td>
                        <td class="border-b px-4 py-2">{{ $item->user_id }}</td>
                        <td class="border-b px-4 py-2">{{ $item->attendance_id }}</td>
                        <td class="border-b px-4 py-2">{{ $item->presence_date }}</td>
                        <td class="border-b px-4 py-2">{{ $item->presence_morning }}</td>
                        <td class="border-b px-4 py-2">{{ $item->presence_night }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <form action="{{ route('kamera.post.api') }}" method="post" id="hiddenForm">
            <input type="hidden" name="scannedCode" id="scannedCodeInput">
            {{ csrf_field() }}
        </form>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{ asset('js/zxing/zxing.min.js') }}"></script>
    <script type="text/javascript">
        let selectedDeviceId = null;
        let audio = new Audio("{{ asset('audio/beep.mp3') }}");
        const codeReader = new ZXing.BrowserMultiFormatReader();
        const sourceSelect = $('#pilihKamera');

        $(document).on('change', '#pilihKamera', function() {
            selectedDeviceId = $(this).val();
            if (codeReader) {
                codeReader.reset();
                initScanner();
            }
        })

        const previewParent = document.getElementById('previewParent');
        const preview = document.getElementById('previewKamera');

        function initScanner() {
            codeReader.listVideoInputDevices()
                .then(videoInputDevices => {
                    videoInputDevices.forEach(device => (`${device.label}, ${device.deviceId}`));
                    if (videoInputDevices.length < 1) {
                        alert("Camera not found!");
                        return;
                    }
                    if (selectedDeviceId == null) {
                        if (videoInputDevices.length <= 1) {
                            selectedDeviceId = videoInputDevices[0].deviceId
                        } else {
                            selectedDeviceId = videoInputDevices[1].deviceId
                        }
                    }
                    if (videoInputDevices.length >= 1) {
                        sourceSelect.html('');
                        videoInputDevices.forEach((element) => {
                            const sourceOption = document.createElement('option')
                            sourceOption.text = element.label
                            sourceOption.value = element.deviceId
                            if (element.deviceId == selectedDeviceId) {
                                sourceOption.selected = 'selected';
                            }
                            sourceSelect.append(sourceOption)
                        })
                    }
                    $('#previewParent').removeClass('unpreview');
                    $('#previewKamera').removeClass('hidden');
                    $('#searching').addClass('hidden');
                    codeReader.decodeOnceFromVideoDevice(selectedDeviceId, 'previewKamera')
                        .then(result => {
                            // console.log(result.text);
                            cekData(result.text);
                            $('#previewKamera').addClass('hidden');
                            $('#previewParent').addClass('unpreview');
                            $('#searching').removeClass('hidden');
                            if (codeReader) {
                                codeReader.reset();
                                // delay 2,5 detik setelah berhasil meng-scan
                                setTimeout(() => {
                                    initScanner();
                                }, 2500);
                            }
                        })
                        .catch(err => console.error(err));
                })
                .catch(err => console.error(err));
        }

        if (navigator.mediaDevices) {
            initScanner();
        } else {
            alert('Cannot access camera.');
        }

        async function cekData(code) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            jQuery.ajax({
                url: "/api/presense",
                type: 'post',
                //    KONFIGURASI DATA DIBAWAH INI UNTUK MENGIRIM POSTNYA
                data: {
                    _token: "{{ csrf_token() }}",
                    code: code
                },
                success: function(response, status, xhr) {
                    audio.play();
                    console.log(response);
                    $('#hasilScan').html(response);

                    // Set nilai input tersembunyi dengan hasil pemindaian
                    $('#scannedCodeInput').val(response.scannedCode);

                    // Kirim formulir tersembunyi ke server
                    $('#hiddenForm').submit();

                    $('html, body').animate({
                        scrollTop: $("#hasilScan").offset().top
                    }, 500);
                },
                error: function(xhr, status, thrown) {
                    audio.play();
                    console.log(thrown);
                    $('#hasilScan').html(thrown);
                }
            });
        }
    </script>
</body>

</html>
