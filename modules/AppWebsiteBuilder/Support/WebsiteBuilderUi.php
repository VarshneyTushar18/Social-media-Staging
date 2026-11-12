<?php

namespace Modules\AppWebsiteBuilder\Support;

use Livewire\Component;

final class WebsiteBuilderUi
{
    /**
     * Show a toast that auto-dismisses (same bus as the app shell).
     */
    public static function toast(Component $livewire, string $type, string $message, ?string $title = null): void
    {
        $detail = array_filter([
            'type' => in_array($type, ['error', 'warning', 'success'], true) ? $type : 'success',
            'title' => $title,
            'message' => $message,
        ], static fn ($value) => $value !== null && $value !== '');

        $livewire->js(sprintf(
            'window.dispatchEvent(new CustomEvent("app-toast", { detail: %s }))',
            json_encode($detail, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
        ));
    }

    public static function toastSuccess(Component $livewire, string $message, ?string $title = null): void
    {
        self::toast($livewire, 'success', $message, $title);
    }

    public static function toastError(Component $livewire, string $message, ?string $title = null): void
    {
        self::toast($livewire, 'error', $message, $title);
    }

    public static function toastWarning(Component $livewire, string $message, ?string $title = null): void
    {
        self::toast($livewire, 'warning', $message, $title);
    }
}
