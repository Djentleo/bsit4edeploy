import csv
import random

# General, realistic, non-location-specific templates for each department/type
TEMPLATES = {
    "fire": [
        "Fire alarm triggered",
        "Sunog na kailangan ng bumbero",
        "Smoke and flames seen",
        "May nasusunog na bahay",
        "Building fire reported",
        "Fire outbreak in the area",
        "Explosion heard, possible fire",
        "Sunog sa residential area",
        "Fire in the kitchen",
        "Fireworks caused a fire",
    ],
    "medical": [
        "Medical emergency",
        "Ambulansya kailangan, may nahimatay",
        "Person collapsed",
        "Senior citizen nadulas, sugatan",
        "Injury reported",
        "Urgent medical assistance needed",
        "May sugatan",
        "Ambulansya tumulong sa aksidente",
        "Heart attack reported",
        "May tumawag ng ambulansya",
    ],
    "police": [
        "Unauthorized access attempt",
        "Magnanakaw nahuli sa tindahan",
        "Street fight reported",
        "Barilan sa pagitan ng dalawang grupo",
        "Protest blocking the street",
        "May lasing na nagwawala",
        "Nanakawan ng cellphone",
        "Multi-vehicle accident",
        "Vehicular collision, may sugatan, emergency response needed",
        "Car collision reported",
    ],
    "tanod": [
        "Flooding in the basement",
        "Malakas na baha, hindi madaanan",
        "Basurang nagbara sa kanal, nagdulot ng pagbaha",
        "May batang nawala, hinahanap ng magulang",
        "Malakas na ingay",
        "Barangay tanod assisted in crowd control",
        "Loud noise complaint",
        "Overflowing river causing flooding",
        "Flooding due to heavy rain",
        "Malakas na sigawan at away",
    ],
}

SEVERITIES = ["low", "medium", "high", "critical"]
TYPES = {
    "fire": "fire",
    "medical": "healthcare",
    "police": random.choice(["vehicle_crash", "public_disturbance"]),
    "tanod": random.choice(["flood", "public_disturbance"]),
}
DEPARTMENTS = ["fire", "medical", "police", "tanod"]


def infer_severity(desc: str, dept: str) -> str:
    d = desc.lower()
    # Strong indicators for critical
    critical_kw = [
        "explosion",
        "barilan",
        "gunshot",
        "heart attack",
        "sunog na kailangan",
        "flames seen",
    ]
    if any(k in d for k in critical_kw):
        return "critical"
    # High severity indicators
    high_kw = [
        "smoke",
        "fire",
        "sunog",
        "vehicular",
        "collision",
        "injury",
        "collapsed",
        "ambulansya",
        "baha",
        "flooding",
    ]
    if any(k in d for k in high_kw):
        return "high"
    # Medium severity
    medium_kw = [
        "protest",
        "away",
        "sigawan",
        "unauthorized access",
        "noise",
        "malakas na ingay",
    ]
    if any(k in d for k in medium_kw):
        return "medium"
    # Department-based fallback
    if dept in ["fire", "medical"]:
        return "high"
    if dept in ["police"]:
        return "medium"
    return "low"


with open("incidents.csv", "w", newline="", encoding="utf-8") as csvfile:
    writer = csv.writer(csvfile)
    writer.writerow(["incident_description", "type", "department", "severity"])
    for _ in range(1000):
        dept = random.choice(DEPARTMENTS)
        desc = random.choice(TEMPLATES[dept])
        # For more realism, randomly mix English/Tagalog and case
        if random.random() < 0.5:
            desc = desc.capitalize()
        else:
            desc = desc.upper()
        # Map department to type
        if dept == "fire":
            typ = "fire"
        elif dept == "medical":
            typ = "healthcare"
        elif dept == "police":
            typ = random.choice(["vehicle_crash", "public_disturbance"])
        else:
            typ = random.choice(["flood", "public_disturbance"])
        severity = infer_severity(desc, dept)
        writer.writerow([desc, typ, dept, severity])
print(
    "Generated 1000 general, realistic, multilingual incident samples in incidents.csv"
)
