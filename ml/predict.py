import json
import sys
from pathlib import Path


ML_DIR = Path(__file__).parent


def main() -> None:
    if len(sys.argv) != 3:
        raise ValueError("expected temperature and duration_minutes")

    temperature = float(sys.argv[1])
    duration_minutes = float(sys.argv[2])
    out_of_range = max(temperature - 6, -temperature, 0)
    import pandas as pd
    from joblib import load

    model = load(ML_DIR / "model.joblib")
    probability = model.predict_proba(
        pd.DataFrame(
            [[temperature, duration_minutes, out_of_range]],
            columns=["temperature", "duration_minutes", "out_of_range"],
        )
    )[0, 1]
    print(
        json.dumps(
            {
                "probability": float(f"{probability:.4f}"),
                "model": "GradientBoosting",
            }
        )
    )


if __name__ == "__main__":
    try:
        main()
    except Exception as error:
        message = str(error) or error.__class__.__name__
        print(json.dumps({"error": message}))
        sys.exit(1)
