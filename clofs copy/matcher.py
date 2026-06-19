import json
from difflib import SequenceMatcher
import sys
print(sys.argv[1], file=sys.stderr)
def similarity(a, b):
    a = a.lower().strip()
    b = b.lower().strip()
    if not a or not b:
        return 0
    return SequenceMatcher(None, a, b).ratio()

if len(sys.argv) < 2:
    print("0")
    sys.exit()

try:
    data = json.loads(sys.argv[1])
except json.JSONDecodeError:
    print("0")
    sys.exit()

# Match values from PHP keys
item_sim = similarity(data.get("claimed_item", ""), data.get("found_item_name", ""))
desc_sim = similarity(data.get("claim_more_details", ""), data.get("found_description", ""))
secret_sim = similarity(data.get("claim_ownership_answer", ""), data.get("found_secret_answer", ""))
optional_sim = similarity(data.get("claim_optional_answer", ""), data.get("found_unique_answer", ""))

# Weights
weights = {
    "item": 30,
    "desc": 30,
    "secret": 30,
    "optional": 10
}

# Score calc
score = 0
score += item_sim * weights["item"]
score += desc_sim * weights["desc"]
score += secret_sim * weights["secret"]

if data.get("claim_optional_answer") and data.get("found_unique_answer"):
    score += optional_sim * weights["optional"]

# Debug (stderr, not visible in PHP)
print(f"item_sim={item_sim}, desc_sim={desc_sim}, secret_sim={secret_sim}, optional_sim={optional_sim}", file=sys.stderr)

# Output integer score
print(int(score))
