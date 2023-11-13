self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    const sendNotification = body => {
        // you could refresh a notification badge here with postMessage API
        const title = "ЗАГОЛООООООВОК";

        return self.registration.showNotification(title, {
            body,
        });
    };

    if (event.data) {
        console.log(event)
        const message = event.data.text();
        // const payload = event.data.json() || { message:'test push!!'};
        event.waitUntil(sendNotification(message));
    }
});
