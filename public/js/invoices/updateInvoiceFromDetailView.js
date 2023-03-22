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
            let statusTextItem = document.querySelector('[data-uuid="' + data.uuid +'"] .status');
            if (!statusTextItem) {
                return;
            }

            statusTextItem.classList.replace("grid-status-" + statusTextItem.textContent, "grid-status-" + data.status)
            statusTextItem.textContent = data.status.toLowerCase();
        }
    }
}