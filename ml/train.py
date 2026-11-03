from pathlib import Path

import numpy as np
import pandas as pd
from joblib import dump
from sklearn.ensemble import GradientBoostingClassifier
from sklearn.metrics import accuracy_score, roc_auc_score
from sklearn.model_selection import train_test_split


ML_DIR = Path(__file__).parent


def main() -> None:
    rng = np.random.default_rng(42)
    row_count = 3000

    in_normal_range = rng.random(row_count) < 0.65
    temperature = np.where(
        in_normal_range,
        rng.uniform(0, 6, row_count),
        rng.normal(7, 5, row_count),
    )
    duration_minutes = np.rint(rng.exponential(25, row_count)).astype(int)
    out_of_range = np.maximum.reduce(
        [temperature - 6, -temperature, np.zeros(row_count)]
    )

    logit = (
        -3.5
        + 0.55 * out_of_range
        + 0.035 * duration_minutes
        + 0.012 * out_of_range * duration_minutes
        + rng.normal(0, 0.6, row_count)
    )
    probability = 1 / (1 + np.exp(-logit))
    risk_label = rng.binomial(1, probability)

    dataset = pd.DataFrame(
        {
            "temperature": temperature,
            "duration_minutes": duration_minutes,
            "out_of_range": out_of_range,
            "risk_label": risk_label,
        }
    )

    features = ["temperature", "duration_minutes", "out_of_range"]
    x_train, x_test, y_train, y_test = train_test_split(
        dataset[features],
        dataset["risk_label"],
        test_size=0.2,
        stratify=dataset["risk_label"],
        random_state=42,
    )
    model = GradientBoostingClassifier(random_state=42)
    model.fit(x_train, y_train)

    predictions = model.predict(x_test)
    probabilities = model.predict_proba(x_test)[:, 1]
    print(f"Accuracy: {accuracy_score(y_test, predictions):.4f}")
    print(f"ROC AUC: {roc_auc_score(y_test, probabilities):.4f}")

    dump(model, ML_DIR / "model.joblib")
    dataset.to_csv(ML_DIR / "dataset.csv", index=False)


if __name__ == "__main__":
    main()
