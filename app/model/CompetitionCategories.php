<?php
/**
 * CompetitionCategories class representing WKF (World Karate Federation)
 * official competition categories.
 *
 * @author Petr
 */
class CompetitionCategories extends Nette\Object {

    /**
     * Returns category string for person with specified attributes
     * 
     * @param char $gender person's gender
     * @param int $age person's age
     * @param double $weight person's weight [kg]
     * @return string WKF category string
     */
    public static function getCategory($gender, $age, $weight) {
        $category = "";
        if (isset($gender) && isset($age) && isset($weight)) {
            if ($gender == "F") {
                $category = self::getFemaleCategory($age, $weight);
            } else {
                $category = self::getMaleCategory($age, $weight);
            }
        } else {
            return "-";
        }
        return $category;
    }

    /**
     * Returns females's categories
     * 
     * @param type $age women's age
     * @param type $weight women's weight [kg]
     * @return string WKF category string
     */
    private static function getFemaleCategory($age, $weight) {
        // Female WKF weights
        $FEMALE_WEIGHT_CATEGORIES = array([35], [42, 50], [47, 54], [48, 53, 59], [50, 55, 61, 68]);
        $category = "";

        if ($age <= 11) {
            $category = "Mladší žákyně" . self::selectWeightCategory($FEMALE_WEIGHT_CATEGORIES[0], $weight);
        } else if ($age <= 13) {
            $category = "Starší žákyně" . self::selectWeightCategory($FEMALE_WEIGHT_CATEGORIES[1], $weight);
        } else if ($age <= 15) {
            $category = "Dorostenky" . self::selectWeightCategory($FEMALE_WEIGHT_CATEGORIES[2], $weight);
        } else if ($age <= 17) {
            $category = "Juniorky" . self::selectWeightCategory($FEMALE_WEIGHT_CATEGORIES[3], $weight);
        } else {
            $category = "Ženy" . self::selectWeightCategory($FEMALE_WEIGHT_CATEGORIES[4], $weight);
        }
        return $category;
    }

    /**
     * Return's male's categories
     * 
     * @param type $age man's age
     * @param type $weight man's weight [kg]
     * @return string WKF category string
     */
    private static function getMaleCategory($age, $weight) {
        // Male WKF weights
        $MALE_WEIGHT_CATEGORIES = array([30, 35, 41], [39, 45, 52, 60], [52, 57, 63, 70], [55, 61, 68, 76], [60, 67, 75, 84]);
        $category = "";

        if ($age <= 11) {
            $category = "Mladší žáci" . self::selectWeightCategory($MALE_WEIGHT_CATEGORIES[0], $weight);
        } else if ($age <= 13) {
            $category = "Starší žáci" . self::selectWeightCategory($MALE_WEIGHT_CATEGORIES[1], $weight);
        } else if ($age <= 15) {
            $category = "Dorostenci" . self::selectWeightCategory($MALE_WEIGHT_CATEGORIES[2], $weight);
        } else if ($age <= 17) {
            $category = "Junioři" . self::selectWeightCategory($MALE_WEIGHT_CATEGORIES[3], $weight);
        } else {
            $category = "Muži" . self::selectWeightCategory($MALE_WEIGHT_CATEGORIES[4], $weight);
        }

        return $category;
    }

    /**
     * Select's weight category
     * 
     * @param array $categoryWeights category weights
     * @param double $weight person's weight [kg]
     * @return string weight category string
     */
    private static function selectWeightCategory($categoryWeights, $weight) {
        $category = "";
        $numberOfCategories = count($categoryWeights);
        for ($i = 0; $i < $numberOfCategories; $i++) {
            if ($weight < $categoryWeights[$i]) {
                $category = $categoryWeights[$i];
            }
        }
        if ($category == "") {
            $category = ", +" . $categoryWeights[$numberOfCategories - 1];
        } else {
            $category = ", -" . $category;
        }
        return $category . "kg";
    }

}
