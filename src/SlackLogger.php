<?php

namespace CrazyFactory\PhalconLogger;

use CrazyFactory\PhalconLogger\LineFormatter;
use CrazyFactory\Slack\SlackClient;
use Phalcon\Config;
use Phalcon\Logger;
use Sentry\State\HubInterface;

/**
 * Logs to the slack.
 */
class SlackLogger extends BaseLogger
{
    /** @var SlackClient */
    private $slackClient;

    /** @var HubInterface */
    private $sentry;

    public function __construct(Config $config, SlackClient $slackClient)
    {
        $this->slackClient = $slackClient;
        parent::__construct($config);
    }

    public function setSentry(HubInterface $sentry)
    {
        $this->sentry = $sentry;
    }

    /**
     * Send to slack if configured.
     */
    protected function logInternal(string $message, int $type, int $time, array $context)
    {
        // format() is required as it does interpolation and other processing required!
        $message = $this->getFormatter()->format($message, $type, $time, $context);

        if ($this->config->slack->level >= $type) {
            $context += ['text' => $message];

            if ($type < Logger::WARNING) {
                $context['channel'] = $this->config->slack->alert_channel;
            }

            if ($this->sentry && $evtId = $this->sentry->getLastEventID()) {
                $context['text'] .= sprintf(' <https://sentry.io/crazyfactory/erp/issues/?query=%s|sentry>', $evtId);
            }

            $this->slackClient->send($context);
        }
    }

    /**
     * Gets a plain line formatter with message format `[%type%] %message%`.
     *
     * @return LineFormatter
     */
    public function getFormatter(): LineFormatter
    {
        if (!$this->_formatter) {
            // The datetime is omitted as the remote services set them when something is logged.
            $this->_formatter = new LineFormatter('[%type%] %message%');
        }

        return $this->_formatter;
    }

}
