<?php

namespace A17\Blast\Components\DocsPages;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use A17\Blast\UiDocsStore;
use Illuminate\Support\Str;

class UiTypesets extends Component
{
    /** @var array */
    public $items;

    public $screens;

    public function __construct(UiDocsStore $uiDocsStore)
    {
        $this->uiDocsStore = $uiDocsStore;
        $this->screens = collect(
            $this->uiDocsStore->get('theme.screens'),
        )->keys();
        $this->fontFamilies = collect(
            $this->uiDocsStore->get('theme.fontFamilies'),
        );
        $typesets = collect($this->uiDocsStore->get('theme.typesets'));

        if ($typesets->isNotEmpty()) {
            $this->items = $typesets->map(function ($item) {
                $row = [];

                $index = 0;
                foreach ($item as $breakpoint => $values) {
                    foreach ($values as $property => $value) {
                        $nextItemKey = array_keys($item)[$index + 1] ?? false;
                        $span = $this->getSpan($breakpoint, null);

                        if ($nextItemKey) {
                            $nextItem = $item[$nextItemKey];

                            if (
                                $nextItem &&
                                array_key_exists($property, $nextItem)
                            ) {
                                $span = $this->getSpan(
                                    $breakpoint,
                                    array_keys($item)[$index + 1] ?? null,
                                );
                            }
                        }

                        if (
                            $property === 'font-family' &&
                            Str::startsWith($value, 'var(--')
                        ) {
                            preg_match('/var\(--(.*)\)/sU', $value, $matches);

                            if (filled($matches)) {
                                $value = $this->fontFamilies->get(
                                    $matches[1],
                                    $value,
                                );
                            }
                        }

                        $row[$property][$breakpoint] = [
                            'span' => $span,
                            'value' => $value,
                        ];
                    }
                    $index++;
                }

                return $row;
            });
        }
    }

    private function getSpan($current, $next)
    {
        $current_index = $this->screens->search($current);
        $next_index = $next
            ? $this->screens->search($next)
            : $this->screens->count();

        return $next_index - $current_index;
    }

    public function bgColor($property = null)
    {
        switch ($property) {
            case 'font-size':
                $color = 'bg-red-100';
                break;

            case 'font-weight':
                $color = 'bg-indigo-100';
                break;

            case 'font-family':
                $color = 'bg-yellow-100';
                break;

            case 'line-height':
                $color = 'bg-blue-100';
                break;

            case 'letter-spacing':
                $color = 'bg-green-100';
                break;

            default:
                $color = 'bg-gray-100';
                break;
        }

        return 'blast-' . $color;
    }

    public function render(): View
    {
        return view('blast::components.ui-docs.typesets');
    }
}