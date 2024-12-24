import {h, Component} from "preact";

export interface NotificationPanelProps {
  // no properties currently
  public_key: string;
}

enum NotificationState {
  Enabled = 'enabled',
  Incompatible = 'incompatible',
  Disabled = 'disabled',
  Computing = 'computing',
  Default = 'default'
}

interface NotificationPanelState {
  // no properties currently
  notifications: NotificationState;
  status: string;
}

function getDefaultState(/*initialControlParams: object*/): NotificationPanelState {
  return {
    notifications: NotificationState.Computing,
    status: 'Normal'
  };
}

function urlBase64ToUint8Array(base64String: string):Uint8Array {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (var i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

// function askPermission(error_callback: any) {
//   return new Promise(function(resolve, reject) {
//     const permissionResult = Notification.requestPermission(function(result) {
//       resolve(result);
//     });
//
//     if (permissionResult) {
//       permissionResult.then(resolve, reject);
//     }
//   })
//     .then(function(permissionResult) {
//       console.log("permissionResult is", permissionResult);
//       if (permissionResult !== 'granted') {
//         error_callback();
//         throw new Error('We weren\'t granted permission.');
//       }
//     });
// }




// function getNotificationPermissionState() {
//   if (navigator.permissions) {
//     return navigator.permissions.query({name: 'notifications'})
//       .then((result) => {
//         return result.state;
//       });
//   }
//
//   return new Promise((resolve) => {
//     resolve(Notification.permission);
//   });
// }




function sendSubscriptionToBackEnd(pushSubscription: PushSubscription, method: string) {
  let result_callback = function(response: any) {
    // TODO - what could we do here? Cancel subscription?
    // pushSubscription.unsubscribe()
    if (!response.ok) {
      throw new Error('Bad status code from server.');
    }

    return response.json();
  };

  let process_callback = function(responseData: any) {
    // TODO - what could we do here? Cancel subscription?
    // pushSubscription.unsubscribe()
    if (!(responseData && responseData.success)) {
      throw new Error('Bad response from server.');
    }
  };

  let options = {
    method: method,
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(pushSubscription)
  };

  return fetch('/api/save-subscription/', options).
    then(result_callback).
    then(process_callback);
}

function processSubscribe(pushSubscription: PushSubscription) {

  // TODO - could save subscription state in local storage.

  console.log('Received PushSubscription: ', JSON.stringify(pushSubscription));
  // now send it to the server
  return sendSubscriptionToBackEnd(pushSubscription, 'POST');
}


export class NotificationRegistrationPanel extends Component<NotificationPanelProps, NotificationPanelState> {

  constructor(props: NotificationPanelProps) {
    super(props);
    this.state = getDefaultState(/*props.initialControlParams*/);
  }

  componentDidMount() {
    let set_state_callback = (values: any) => { this.setState(values) };

    navigator.permissions.query({
     name: 'notifications'
    }).then(function(result) {
      console.log("NotificationState is ",  result.state);
      if (result.state == 'granted') {
        set_state_callback({notifications: NotificationState.Enabled})
      }
      else if (result.state == 'denied') {
        set_state_callback({notifications: NotificationState.Disabled})
      }
      // @ts-ignore: pretty sure that both are possible
      else if (result.state == 'default' || result.state == 'prompt') {
        set_state_callback({notifications: NotificationState.Default})
      }
    })
  }


  componentWillUnmount() {
  }

  startSubscribe(serviceWorkerRegistration: ServiceWorkerRegistration): any {
    const subscribeOptions = {
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(
        this.props.public_key
      )
    };

    return serviceWorkerRegistration.
      pushManager.
      subscribe(subscribeOptions).
      then(processSubscribe).then(
        () => { this.setState({notifications: NotificationState.Enabled}) }
    );
  }

  unsubscribeFromNotifications(serviceWorkerRegistration: ServiceWorkerRegistration) {

    let set_state_callback = () => this.setState({notifications: NotificationState.Disabled});

    console.log("Inside unsubscribeFromNotifications");
    // To unsubscribe from push messaging, you need get the
    // subscription object, which you can call unsubscribe() on.
    let error_callback = function(e: Error) {
      console.log('Error while unsubscribing to notifications: ', e);
      set_state_callback();
    };

    let success_callback = function(successful: boolean) {
      console.log("unsubscribeFromNotifications success_callback", successful);
      set_state_callback();
    }

    let process_unsubscribe_callback = function(pushSubscription: PushSubscription) {
      console.log('Inside process_unsubscribe_callback');
      // Check we have a subscription to unsubscribe
      if (!pushSubscription) {
        // "No subscription object, so set the state
        // to allow the user to subscribe to push"
        // Ugh - that seems an assumption
        // this.setState({notifications: NotificationState.Disabled});
        set_state_callback();
        return;
      }

      try {
        // TODO - need to delete from server.
        // push_sendSubscriptionToServer(pushSubscription, 'delete');
        // We have a subscription, so call unsubscribe on it
        console.log("pushSubscription.unsubscribe");
        pushSubscription.unsubscribe().then(success_callback).catch(error_callback);
        // sendSubscriptionToBackEnd(pushSubscription, 'DELETE');

      }
      catch (err) {
        console.log("Some sort of error", err);
      }
    };

    serviceWorkerRegistration.
      pushManager.
      getSubscription().
      then(process_unsubscribe_callback).
      catch(error_callback);
  }

  handleDisallowNotificationsClick() {
    console.log("Remove push notifications");
    this.setState({notifications: NotificationState.Computing});
    // navigator.serviceWorker.ready.then(() => console.log("at least is called?"));

    navigator.serviceWorker.ready.then(
      (serviceWorkerRegistration: ServiceWorkerRegistration) => this.unsubscribeFromNotifications(serviceWorkerRegistration)
    );

    console.log("end of handleDisallowNotificationsClick");
  }

  askPermission () {
    if (!("Notification" in window)) {
      // Check if the browser supports notifications
      // alert("This browser does not support desktop notification");
      this.setState({status: "This browser does not support desktop notification"});
    } else if (Notification.permission === "granted") {
      this.setState({status: 'granted'});
    } else if (Notification.permission !== "denied") {
      // We need to ask the user for permission
      Notification.requestPermission().then((permission) => {
        // If the user accepts, let's create a notification
        if (permission === "granted") {
          // const notification = new Notification("Hi there!");
          this.setState({status: 'Notification granted'});
        }
      });
    }

    // Last, if the user has denied notifications, and you
    // want to be respectful there is no need to bother them anymore.
    this.setState({status: 'Notification denied'});
  }


  handleAllowNotificationsClick() {
    if (!('serviceWorker' in navigator)) {
      console.log('Service Worker isn\'t supported on this browser, disable or hide UI.');
      this.setState({notifications: NotificationState.Incompatible})
      return;
    }

    if (!('PushManager' in window)) {
      // Push isn't supported on this browser, disable or hide UI.
      console.log('Push isn\'t supported on this browser, disable or hide UI.');
      this.setState({notifications: NotificationState.Incompatible})
      return;
    }



    this.askPermission();

    navigator.serviceWorker.ready.then(
      (serviceWorkerRegistration: ServiceWorkerRegistration) => this.startSubscribe(serviceWorkerRegistration)
    );
  }




  renderNotificationButton() {
    switch (this.state.notifications) {
      case NotificationState.Enabled:
        return <span>
          Notifications are enabled.
          <span className='notifications_control' onClick={() => this.handleDisallowNotificationsClick()}>
            Click to disable notifications</span>
        </span>
      case NotificationState.Disabled:
        return <span>Notifications not currently allowed.
          <span className='notifications_control' onClick={() => this.handleAllowNotificationsClick()}>
            Click to allow notifications
          </span>
        </span>
      case NotificationState.Computing:
        return <span>Determining if notifications are allowed.</span>
      case NotificationState.Incompatible:
        return <span>Notifications not allowed/supported in this browser.</span>
    }
  }

  render(props: NotificationPanelProps, state: NotificationPanelState) {
    let notification_button = this.renderNotificationButton();
    return  <div class='notifications_panel_react'>
      <h3>Notifications</h3>
      {notification_button}
      Status: {this.state.status}
      <span>
          <span className='notifications_control' onClick={() => this.handleAllowNotificationsClick()}>
            Force reset notifications.
          </span>
        </span>
    </div>;
  }
}
