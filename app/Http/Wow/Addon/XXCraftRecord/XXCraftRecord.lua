local XXCraftRecordIndex = 0
XXCraftHistory = {}

local frame = CreateFrame("Frame")
frame:RegisterEvent("CHAT_MSG_LOOT")
frame:SetScript("OnEvent", function(self, event, text)
    if event == 'CHAT_MSG_LOOT' then
        XXCraftHistory[XXCraftRecordIndex..':'..time()] = text
        XXCraftRecordIndex = XXCraftRecordIndex + 1
    end
end)

