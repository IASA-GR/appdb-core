# How it works

The OpenID connect sub service is issuing, manages and stores the access/refresh tokens. It can handle different services requesting to issue
access tokens with different configurations and in a way scopes the process and management of access token to each of these services. It is composed
by the storage and a list of services (requestors).The storage mechanism is common to all the services and stores the access and refresh tokens separately
for each service.

Each service has its own configuration as to the issuer, scopes and general meta data. The also have their own client type module that dictates
from which endpoints are available and what permissions these endpoints have regarding the access to the information (Eg if they can read access tokens of users)


# Configure AAI OpenID Connect service

## Storage module

The storage module may have a lot of implementations configured by the "type" property. Currently only the "filesystem" type is supported.

|  Name | Value | Optional | Description |
| ------------ | ------------ | ------------ | ------------ |
| type | string | false | The name of the storage type. Eg "fielsystem"|
| encryption | string | true | The name of the encryption to be used, eg, "openssl"|

### Filesystem storage module (extends the storage module)

|  Name | Value | Optional | Description |
| ------------ | ------------ | ------------ | ------------ |
|  path | &lt;*file system path*&gt;  | false  | The absolute file path to store tokens. If it does **not** exist, the service will try to create it. |

## Storage Encryption module

This module encrypts the information before it is stored. Currently only "openssl" encryption is supported.

### Openssl encrytpion module

The OpenSSL encryption module has a fixed way of encrypting the information and no further properties are needed.

##  Service module
|  Name | Type | Optional | Description |
| ------------ | ------------ | ------------ | ------------ |
| enabled |  boolean  | false  | If false the service is ignored. |
| service | string  |  false | The short name of the service. |
| service_name | string  |  false | The long name (title) of the service. |
| service_logo | url  | true | The URL of the service logo. |
| scopes | string[] | false | The list of scopes under which the service will request a new access token (eg. openid,offline_access,email,profile) |
| client_id | string  |  false | The client id as given by the issuer (eg EGI AAI) |
| client_secret | string | false | The client secret as given by the issuer (eg EGI AAI) |
| issuer | URL | false | The URL from where the sevice will issue(request) the access tokens (eg https://aai-dev.egi.eu/oidc/) |
| redirect_url | URL | false | The URL to provide to the issuer as a callback after a successful transaction |
| token_expiration | integer | false | The default seconds to be used as an offset in case the issuer won't provide the epxiration timestamp |
| refresh_token_expiration | integer | false | The offset in seconds to be used as the refresh token expiration date |
| actions | string[] |  false | A list of actions descriptions that the service will be used for (eg manage VMs) |
| allowed_referrers | string[] | true | A list of allowed domains that the service allows as source of requests (eg https://*.egi.eu) |
| account_epuid | string | true | If given then the specific service will publish and provide access tokens only for the given account epuid. This option is given primary for service accounts |


## Client module

The client module is configured for under a specific service. It handles incoming requests and validates them. In case of many clients
the system will check each configured client and return the first one that is reported as valid from its authorization module and permissions set.
If no client is valid the request will return an error.

| Name | Type | Optional | Description |
| ------------ | ------------ | ------------ | ------------ |
| enabled | boolean | false | If false the client is ignored. |
| auth | config | true | Configuration for the Authorization module to be used from this client |
| auth.type| string | false | The type of authorization module to be used for this client |
| auth.params | string[] | false | Object with parameters to be passed to the specific authorization module of this client |
| perms | config | true | Configuration for the permissions this client has. |

## Authorization module

This module performs checks that a request is valid. It is used by the Client module.

### BearerToken Authorization module parameters

This module checks for specific token in the HTTP header in order to report the request as valid. In case
a list of valid ips is provided then these are also checked against the IP that the request originated from.

| Name | Type | Optional | Description |
| ------------ | ------------ | ------------ | ------------ |
| token | string | false | The token to be passed in the HTTP headers as 'Authorization: Bearer <TOKEN>'|
| valid_ips | string[] | true | A list of allowed IPs for this authorization|

### Client permissions set

This is a set of permissions that a client has. These permissions are not automatically applied to actions
but can be queried by the calling code to decide if a request can perform read access token operations.

| Name | Description |
| ---- | ----------- |
| view_access_tokens | The client can be used to retrieve the access tokens of the given user UID. In order to retrieve the user UID the client will look for the HTTP header 'X-UID' |

## Pass configuration to application.ini
The AppDB will query configuration variables under the base path of **service.aaioidc**. After that the folowing schema is defined:

| Name | Type | Description |
| ---- | ---- | ----------- |
| enabled | boolean | If false the service is disabled |
| storage | config | The configuration of the storage module to be used |
| service.<service_name> | config | The configuration of each service used under to handle the OpenID Connect flow |
| clients.<service_name> | config | The configuration of clients to be used for each service |

The list of strings is achived by providing the all the values as a single string seperated with a semicolon.

An example for the VMOps service is given bellow:

```ini
service.aaioidc.enabled='true'
service.aaioidc.storage.type = 'filesystem'
service.aaioidc.storage.path = '/absolute/path/to/tokens/storage';
service.aaioidc.storage.encryption = 'openssl'
service.aaioidc.service.vmops.enabled='true'
service.aaioidc.service.vmops.service='vmops'
service.aaioidc.service.vmops.service_name='EGI AppDB VMOPs service';
service.aaioidc.service.vmops.service_logo='https://appdb.egi.eu/images/appdblogo.png';
service.aaioidc.service.vmops.scopes='openid;offline_access;email;profile'
service.aaioidc.service.vmops.client_id='12345'
service.aaioidc.service.vmops.client_secret='absde'
service.aaioidc.service.vmops.issuer='https://aai-dev.egi.eu/oidc/'
service.aaioidc.service.vmops.redirect_url='https://appdb.egi.eu/aaioidc/refreshtoken'
service.aaioidc.service.vmops.token_expiration='3600'
service.aaioidc.service.vmops.refresh_token_expiration='1036800'
service.aaioidc.service.vmops.actions='manage VMs'
service.aaioidc.service.vmops.allowed_referrers='https://*.egi.eu'
service.aaioidc.clients.vmops.enabled='true'
service.aaioidc.clients.vmops.auth.type='BearerToken'
service.aaioidc.clients.vmops.auth.params.token='1234'
service.aaioidc.clients.vmops.auth.params.valid_ips='127.0.0.1';
service.aaioidc.clients.vmops.perms.view_access_tokens = 'true'
```
