class UpdateStatusSse {
    sseUrl;
    topic;

    constructor(sseUrl, topic) {
        this.sseUrl = sseUrl;
        this.topic = topic;
    }

    execute() {
        const url = new URL(this.sseUrl);

        url.searchParams.append('topic', this.topic);
        const eventSource = new EventSource(url);

        eventSource.onmessage = (msg) => {
            const data = JSON.parse(msg.data);
            addInvoiceSnackBar(data);
            this.changeValueInGrid(data);
        }
    }

    changeValueInGrid(data) {
        let selectors = 'tr[data-uuid="' + data.uuid + '"] > .status > span';
        let statusTextItem = document.querySelector(selectors);
        if (!statusTextItem) {
            return;
        }

        statusTextItem.classList.replace("grid-status-" + statusTextItem.textContent, "grid-status-" + data.status)
        statusTextItem.textContent = data.status.toLowerCase();
    }
}
