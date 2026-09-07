<?php

require __DIR__ . '/../upload/src/addons/WarextStudios/ThreadFreshness/Util/Eligibility.php';

use WarextStudios\ThreadFreshness\Util\Eligibility;

$now = 2_000_000_000;

$ids = Eligibility::parseForumIds('4, 2;4 8 invalid 0 -2');

if ($ids !== [2, 4, 8])
{
    fwrite(STDERR, "Forum ID ayrıştırma testi başarısız.\n");
    exit(1);
}

if (!Eligibility::isThreadEligible(true, 4, $now - 91 * 86400, [4, 8], 90, $now))
{
    fwrite(STDERR, "Konu uygunluk testi başarısız.\n");
    exit(1);
}

if (Eligibility::isThreadEligible(true, 4, $now - 89 * 86400, [4, 8], 90, $now))
{
    fwrite(STDERR, "Bekleme süresi testi başarısız.\n");
    exit(1);
}

if (Eligibility::isThreadEligible(true, 7, $now - 365 * 86400, [4, 8], 90, $now))
{
    fwrite(STDERR, "Forum seçimi testi başarısız.\n");
    exit(1);
}

if (!Eligibility::canVisitorVote(10, 20, $now - 30 * 86400, 25, true, false, 7, 3, $now))
{
    fwrite(STDERR, "Oy uygunluk testi başarısız.\n");
    exit(1);
}

if (Eligibility::canVisitorVote(10, 10, $now - 30 * 86400, 25, true, false, 7, 3, $now))
{
    fwrite(STDERR, "Kendi konusuna oy testi başarısız.\n");
    exit(1);
}

if (Eligibility::canVisitorVote(10, 20, $now - 2 * 86400, 25, true, false, 7, 3, $now))
{
    fwrite(STDERR, "Hesap yaşı testi başarısız.\n");
    exit(1);
}

if (Eligibility::canVisitorVote(10, 20, $now - 30 * 86400, 1, true, false, 7, 3, $now))
{
    fwrite(STDERR, "Mesaj sınırı testi başarısız.\n");
    exit(1);
}

if (Eligibility::canVisitorVote(10, 20, $now - 30 * 86400, 25, false, false, 7, 3, $now))
{
    fwrite(STDERR, "Yetki testi başarısız.\n");
    exit(1);
}

echo "OK\n";
