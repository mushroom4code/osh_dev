<?php

namespace Mrden\MkadDistance\Geometry\Polygon;

use Mrden\MkadDistance\Geometry\Polygon;

final class SpbKadJunctions extends Polygon
{
    private const POLYGON_DATA = [
        [60.038298, 29.968254],
        [60.038866, 29.973156],
        [60.039200, 30.000145],
        [60.038812, 29.996561],
        [60.056463, 30.127432],
        [60.057997, 30.135800],
        [60.057997, 30.135800],
        [60.061221, 30.154468],
        [60.099397, 30.279194],
        [60.098605, 30.270530],
        [60.091402, 30.368782],
        [60.061497, 30.387386],
        [60.053833, 30.400103],
        [60.047853, 30.422916],
        [60.047866, 30.423731],
        [60.038081, 30.439591],
        [60.037180, 30.440156],
        [60.035867, 30.440231],
        [60.032080, 30.443694],
        [60.017502, 30.460999],
        [59.993247, 30.479673],
        [59.986590, 30.490331],
        [59.945878, 30.541000],
        [59.891939, 30.523727],
        [59.892395, 30.524084],
        [59.858955, 30.514593],
        [59.855217, 30.507406],
        [59.853797, 30.484109],
        [59.825460, 30.433444],
        [59.825460, 30.433444],
        [59.823346, 30.426082],
        [59.815291, 30.371445],
        [59.815103, 30.360607],
        [59.813223, 30.346841],
        [59.810562, 30.337768],
        [59.814433, 30.315144],
        [59.816123, 30.311754],
        [59.799736, 30.156686],
        [59.816313, 30.073920],
        [59.816045, 30.070400],
        [59.815028, 29.927055],
        [59.826982, 29.825037],
        [59.829529, 29.821296],
        [59.859664, 29.801441],
        [59.862890, 29.795462],
        [59.919763, 29.664256],
        [59.921601, 29.665049],
        [60.010877, 29.715056],
        [60.013829, 29.718846],
    ];

    /**
     * MoscowMkad constructor.
     */
    public function __construct()
    {
        parent::__construct(self::POLYGON_DATA);
    }

    public function __toString()
    {
        return 'St. Petersburg KAD Junctions';
    }
}