class UpdateInvoiceFromInvoiceForm {
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
        }
    }
}

