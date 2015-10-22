# PocketTelegram
The Telegram Bot API for PocketMine-MP with many features

## Configuration
> File location: PocketMine-MP/plugins/PocketTelegram/config.yml

### Basic configurations
| Field | Description | Value |
| :--- | --- | :---: |
| token | Your Telegram bot's token. If you not have any bots, talk to [@BotFather](https://core.telegram.org/bots#botfather) and create your first bot. | **Required** |
| defaultChannel | The chat_id of your Telegram group or the public name of your Telegram channel, e.g. `-23612486`, `"@chalkpe_status"`. **Note** that the bot must be joined to the group or be an administrator of the channel. You can get your chat_id of the group by using the `/chat_id` command in there. | _Optional_ |
| updateInterval | The interval of the bot updating messages from the Telegram API. A second is equals to 20. If it's a negative value, the updating will be disabled. | Default: `20` |

### Link configurations
| Field | Description | Value |
| :--- | --- | :---: |
| broadcastToTelegram | Broadcasts the PocketMine's player activities to your Telegram group/channel automatically. | Default: `true` |
| broadcastTelegramMessages | Broadcasts the Telegram group's user messages to your server automatically. | Default: `true` |
| enableTelegramCommands | Handles the command messages from the Telegram, e.g. `/chat_id`, `/online`. | Default: `true` |

### Advanced Bot API configurations
| Field | Description | Value |
| :--- | --- | :---: |
| disableWebPagePreview | Disables link previews for links in your messages. | Default: `true` |
| enableMarkdownParsing | Applies basic [Markdown syntax](https://core.telegram.org/bots/api#using-markdown) to your messages. | Default: `false` |

### Developer options
| Field | Description | Value |
| :--- | --- | :---: |
| debugMode | Shows the Telegram Bot API responses to the server console. | Default: `false` |

## License
```
Copyright (C) 2015  ChalkPE

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published
by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
```
