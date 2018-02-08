# WebSocketFly
A websocket server based on swoole.

## Characteristic
* use pipleline and middlewares to handle handshake/open/message
* middlewares are loded when swoole worker starting, not swoole server starting. This desgin allows Hot Reload On Code Change.