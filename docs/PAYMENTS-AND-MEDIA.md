# Payments, Membership, and Lesson Media

## Membership model

FastPhunzira supports two student membership states:

- **Regular**: default student membership; can access regular courses.
- **Premium**: active paid membership; can access regular and premium courses.

Courses have an `access_tier` of `regular` or `premium`. Premium access is enforced server-side in enrollment, lesson learning, quiz access, and exam access. The UI is not treated as an authorization boundary.

## PayChangu

PayChangu is the payment provider for Premium membership. The application uses PayChangu Standard Checkout so the payment page remains hosted by PayChangu. The server creates a unique transaction reference, stores a pending transaction, redirects the student to PayChangu, and verifies the transaction before activating Premium membership.

Required server environment variables:

    PAYCHANGU_ENABLED=false
    PAYCHANGU_MODE=test
    PAYCHANGU_CURRENCY=MWK
    PAYCHANGU_PUBLIC_KEY=
    PAYCHANGU_SECRET_KEY=
    PAYCHANGU_WEBHOOK_SECRET=
    PAYCHANGU_API_BASE_URL=https://api.paychangu.com

The secret and webhook credentials must never be placed in GitHub or the platform settings table.

The admin Settings screen controls the Premium price, currency, and membership duration. PayChangu credential status is displayed there without exposing secret values.

### Payment security

A successful browser redirect is not trusted by itself. The server calls PayChangu's verification endpoint and checks:

1. transaction reference matches the locally created transaction;
2. provider status is successful;
3. currency matches;
4. amount paid is at least the configured amount.

The webhook endpoint validates the PayChangu signature and then performs server-side transaction verification before activating membership.

## Lesson video model

Lessons support:

1. YouTube URLs, rendered using the YouTube privacy-enhanced embed host.
2. External hosted video URLs, rendered with an HTML5 player with download controls disabled.

The current FastPhunzira hosting environment should **not** be used as a video CDN. InfinityFree's current free-hosting guidance/community reports a 10 MB limit for non-HTML uploaded files, and the free service also enforces resource limits. Large lesson videos should therefore live on a dedicated video host/CDN rather than inside the PHP application.

### About "impossible to download"

A normal browser video player cannot guarantee that a video is impossible to download or capture. Disabling the download button, hiding direct links, requiring authentication, and streaming through protected endpoints are deterrents and access controls, not DRM.

For genuinely protected premium video, the production architecture should use a video service supporting signed/expiring playback URLs and, where the content value justifies it, DRM. The PHP application should authorize the student and issue only a short-lived playback URL.

For YouTube, FastPhunzira can embed videos, but YouTube links are not equivalent to private DRM-protected course hosting. Unlisted videos can still be shared by anyone who obtains the link.

## Recommended media flow

    Student opens lesson
            ↓
    Server checks authentication
            ↓
    Server checks enrollment
            ↓
    Server checks Regular/Premium access
            ↓
    Lesson view loads
            ↓
    YouTube embed OR protected external player

Large uploaded files should not be committed to GitHub or stored in the public web root.


## Lesson study materials and local media

Lessons now support a server-side media layer separate from the PHP source:

- local lesson video uploads;
- PDF, audio, video, HTML, document, and text study materials;
- per-material download permission;
- protected view/download routes;
- an in-application PDF iframe viewer;
- protected local-video streaming with byte-range support.

Uploaded files are stored under storage/learning-media by default and are ignored by Git. The storage path can be changed with MEDIA_STORAGE_PATH.

The default application upload limit is 10 MB and can be changed with MEDIA_MAX_UPLOAD_BYTES, but the effective limit is also constrained by PHP/server settings such as upload_max_filesize and post_max_size. Large production videos should still move to object storage or a dedicated video provider.

Student media access is checked server-side for authentication, enrollment, published course status, and Premium membership where required. A disabled download button is not DRM: if a browser can render content, a determined user can still capture or copy it.

HTML materials are served with a restrictive sandbox Content Security Policy so uploaded HTML cannot execute arbitrary application-origin scripts. Arbitrary PHP/script uploads are not allowed by the media MIME allowlist.
