(function () {
    const times = document.querySelectorAll('time[datetime]');

    times.forEach((time) => {
        const raw = time.getAttribute('datetime');
        if (!raw) {
            return;
        }

        const date = new Date(raw.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return;
        }

        time.title = date.toLocaleString('ja-JP', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        });
    });
})();
