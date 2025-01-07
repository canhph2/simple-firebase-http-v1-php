# A Simple Firebase library using HTTP v1 API for PHP
- Forked from author: Cong Nguyen Quang
- Docs: https://firebase.google.com/docs/cloud-messaging/migrate-v1
- Reference the 3rd library: https://github.com/kreait/firebase-php

## How to install
```shell
composer require canhph2/simple-firebase-http-v1-php
```

## Required
- The `firebase-service-account-credentials.json` in your server (or your local machine),
  - Download the file in Firebase > Project settings > Service Account > Generate new private key
- Set the environment variable GOOGLE_APPLICATION_CREDENTIALS to the file path of the JSON file that contains your service account key.
```shell
export GOOGLE_APPLICATION_CREDENTIALS="/<FULL PATH OF>/service-account-file.json"
```

---

## DevOps
### Release a new version
```shell
sh .ops/release-a-new-version.sh
```
