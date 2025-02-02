<?php

/*
 * This file is part of zerosonesfun/flarum-inline-audio.
 *
 * Copyright (c) 2021 Billy Wilcosky.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Zerosonesfun\InlineAudio;

use Flarum\Extend;
use Flarum\Frontend\Document;
use s9e\TextFormatter\Configurator;

return [
    (new Extend\Frontend('forum'))
        ->content(function (Document $document) { $document->head[] = '<script src="/assets/extensions/zerosonesfun-inline-audio/sm2.js"></script><script src="/assets/extensions/zerosonesfun-inline-audio/inline-player.js"></script><script src="/assets/extensions/zerosonesfun-inline-audio/reboot.js"></script>'; })
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/resources/less/forum.less'),

    new Extend\Locales(__DIR__ . '/resources/locale'),
    (new Extend\Formatter)
        ->configure(function (Configurator $config) {
            // Define a custom BBCode with one attribute "meta" and inner content.
            // The expected meta format is:
            //    "<some-text>, post:<post-number>, topic:<topic-number>, username:<username>"
            $config->BBCodes->addCustom(
                '[quote="{meta}"]{content}[/quote]',
                // Use a callable replacement to parse the "meta" attribute.
                function (array $match) {
                    // The attribute is available as $match['meta'].
                    $meta = $match['meta'];
                    $content = $match['content'];

                    // Attempt to parse the meta using a regular expression.
                    // This regex assumes:
                    //  - Some initial text (captured in group 1)
                    //  - Followed by ", post:" then a series of digits (group 2)
                    //  - Followed by ", topic:" then a series of digits (group 3)
                    //  - Followed by ", username:" then a non-space string (group 4)
                    if (preg_match('/^(.*?),\s*post:(\d+),\s*topic:(\d+),\s*username:(\S+)$/', $meta, $matches)) {
                        $text     = trim($matches[1]);
                        $post     = trim($matches[2]);
                        $topic    = trim($matches[3]);
                        $username = trim($matches[4]);

                        // Build the HTML output.
                        $html = '<blockquote>';
                        $html .= '<div class="quote-meta">';
                        // Optionally escape output here if needed.
                        $html .= htmlspecialchars($text) . ' ';
                        $html .= '<a href="https://shuiyuan.sjtu.edu.cn/t/topic/' . htmlspecialchars($post) . '">Post ' . htmlspecialchars($post) . '</a>, ';
                        $html .= '<a href="https://shuiyuan.sjtu.edu.cn/t/topic/' . htmlspecialchars($post) . '/' . htmlspecialchars($topic) . '">Topic ' . htmlspecialchars($topic) . '</a>, ';
                        $html .= '<a href="https://shuiyuan.sjtu.edu.cn/u/' . htmlspecialchars($username) . '">' . htmlspecialchars($username) . '</a>';
                        $html .= '</div>';
                        $html .= '<div class="quote-content">' . $content . '</div>';
                        $html .= '</blockquote>';

                        return $html;
                    }

                    // If the meta does not match the expected format,
                    // fall back to a simple blockquote.
                    return '<blockquote><div class="quote-content">' . $content . '</div></blockquote>';
                }
            );
        }),
        (new Extend\Formatter)
            ->configure(function (Configurator $config) {
                $config->BBCodes->addCustom(
                    '[player]{URL}[/player]',
                    '<audio controls src="{URL}"></audio>'
                );
            })
];
